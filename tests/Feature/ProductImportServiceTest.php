<?php

namespace Tests\Feature;

use App\Models\ProductData;
use App\Services\ProductImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductImportServiceTest extends TestCase
{

    public function testImportProducts()
    {
        $file = base_path('tests\data\stock.csv');
        $service = new ProductImportService();

        // Delete All existing data from the database table to fresh start before testing begins
        ProductData::truncate();

        // Test mode
        $report = $service->import($file, true);
        
        $this->assertEquals(29, $report['total']);
        $this->assertEquals(24, $report['success']);
        $this->assertEquals(3, $report['skipped']);
        $this->assertEquals(2, count($report['failed']));
        $this->assertCount(0, ProductData::all());
        
        // Normal mode
        $service->reset();
        $report = $service->import($file);
        
        $this->assertEquals(29, $report['total']);
        $this->assertEquals(24, $report['success']);
        $this->assertEquals(3, $report['skipped']);
        $this->assertEquals(2, count($report['failed']));
        $this->assertDatabaseCount('tblProductData', 24);

        // Verify specific records
        $this->assertDatabaseHas('tblProductData', ['strProductCode' => 'P0001']);
        $this->assertDatabaseHas('tblProductData', ['strProductCode' => 'P0026']);
    }
}