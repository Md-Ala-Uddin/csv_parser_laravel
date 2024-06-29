<?php

namespace App\Console\Commands;

use App\Services\ProductImportService;
use Illuminate\Console\Command;

class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:products {file} {--test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from a CSV file';

    protected $productImportService;

    public function __construct(ProductImportService $productImportService)
    {
        parent::__construct();
        $this->productImportService = $productImportService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $isTestMode = $this->option('test');

        $report = $this->productImportService->import($file, $isTestMode);

        $this->info("Total records processed: {$report['total']}");
        $this->info("Successfully imported: {$report['success']}");
        $this->info("Skipped: {$report['skipped']}");

        $totalFailed = count($report['failed']);

        $this->warn("Failed: {$totalFailed}");
        
        if ($totalFailed > 0) {
            $this->warn("Failed records:");

            // Show failed records in a tabular format with reason for failing.
            $headers = ['Product Code', 'Product Name', 'Stock', 'Cost in GBP', 'Discontinued','Reason'];
            $this->table($headers, $report['failed']);
        }

    }
}
