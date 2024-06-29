<?php

namespace App\Services;

use App\Models\ProductData;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use League\Csv\Statement;

class ProductImportService
{
    protected $isTestMode;
    protected $failedRecords = [];
    protected $total = 0;
    protected $success = 0;
    protected $skipped = 0;

    /**
     * Import product data from a CSV file.
     *
     * @param string $file The path to the CSV file.
     * @param bool $isTestMode If true, the import will run in test mode without saving data to the database.
     * @return array A report of the import process, including total, success, skipped, and failed records.
     */
    public function import($file, $isTestMode = false)
    {
        $this->isTestMode = $isTestMode;

        // Initialize CSV reader
        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(0);

        // Process CSV in chunks
        $chunkSize = 1000; // Initial chunk size
        $offset = 0;

        do {
            // Adjust chunk size for the last chunk
            $stmt = (new Statement())->offset($offset)->limit($chunkSize);
            $records = $stmt->process($csv);

            foreach ($records as $record) {
                $this->total++;

                // Validate record before processing
                $validator = $this->validateRecord($record);
                if ($validator->fails()) {
                    $this->addFailedRecord($record, $validator->errors()->all());
                    continue;
                }

                // Ignore record if it doesnt fulfill the business logic
                if ($this->shouldSkip($record)) {
                    $this->skipped++;
                    continue;
                }

                try {
                    if (!$this->isTestMode) {
                        ProductData::create($this->transformRecord($record));
                    }
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                    $this->addFailedRecord($record, [$e->getMessage()]);
                    continue;
                }

                $this->success++;
            }

            // Update offset for next chunk
            $offset += $chunkSize;
        } while (count($records) > 0); // Continue until no more records

        return $this->getReport();
    }

    private function validateRecord($record)
    {
        // Define validation rules for each field
        return Validator::make($record, [
            'Product Code' => 'required|string|unique:tblProductData,strProductCode',
            'Product Name' => 'required|string',
            'Product Description' => 'nullable|string',
            'Stock' => 'nullable|integer',
            'Cost in GBP' => 'nullable|numeric',
            'Discontinued' => 'nullable|in:null,yes',
        ]);
    }

    /**
     * Reset the state of the service.
     */
    public function reset()
    {
        $this->isTestMode = false;
        $this->failedRecords = [];
        $this->total = 0;
        $this->success = 0;
        $this->skipped = 0;
    }

    /**
     * Determine if a record should be skipped based on business logic.
     *
     * @param array $record The CSV record being processed.
     * @return bool True if the record should be skipped, false otherwise.
     */
    public function shouldSkip($record)
    {
        $price = (float) $record['Cost in GBP'];
        $stockLevel = (int) $record['Stock'];

        return ($price < 5 && $stockLevel < 10) || $price > 1000;
    }

    private function addFailedRecord($record, $errors)
    {
        Log::error('Validation Error', $errors);

        $this->failedRecords[] = [
            'Product Code' => $record['Product Code'],
            'Product Name' => $record['Product Name'],
            'Stock' => $record['Stock'],
            'Cost in GBP' => $record['Cost in GBP'],
            'Discontinued' => $record['Discontinued'],
            'errors' => implode(' ', $errors)
        ];
    }

    /**
     * Transform a CSV record into a format suitable for database insertion.
     *
     * @param array $record The CSV record being processed.
     * @return array The transformed record.
     */
    private function transformRecord($record)
    {
        return [
            'strProductName' => $record['Product Name'],
            'strProductDesc' => $record['Product Description'],
            'strProductCode' => $record['Product Code'],
            'stock_level' => $record['Stock'],
            'price' => $record['Cost in GBP'],
            'dtmDiscontinued' => $record['Discontinued'] === 'yes' ? Carbon::now() : null
        ];
    }

    private function getReport()
    {
        return [
            'total' => $this->total,
            'success' => $this->success,
            'skipped' => $this->skipped,
            'failed' => $this->failedRecords,
        ];
    }
}
