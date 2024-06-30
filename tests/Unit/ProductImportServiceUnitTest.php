<?php

namespace Tests\Unit;

use App\Services\ProductImportService;
use PHPUnit\Framework\TestCase;

class ProductImportServiceUnitTest extends TestCase
{
    public function testShouldSkip()
    {
        $service = new ProductImportService();

        // Test case where price < 5 and stock level < 10
        $record = ['Cost in GBP' => '4.99', 'Stock' => '9'];
        $this->assertTrue($service->shouldSkip($record));

        // Test case where price > 1000
        $record = ['Cost in GBP' => '1001', 'Stock' => '50'];
        $this->assertTrue($service->shouldSkip($record));

        // Test case where price >= 5 and <= 1000, stock level >= 10
        $record = ['Cost in GBP' => '500', 'Stock' => '20'];
        $this->assertFalse($service->shouldSkip($record));

        // Test case where price < 5 but stock level >= 10
        $record = ['Cost in GBP' => '4.99', 'Stock' => '10'];
        $this->assertFalse($service->shouldSkip($record));

        // Test case where price >= 5 but stock level < 10
        $record = ['Cost in GBP' => '5', 'Stock' => '9'];
        $this->assertFalse($service->shouldSkip($record));
    }
}
