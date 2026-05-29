<?php
require_once __DIR__ . '/../src/Catalog.php';
use PHPUnit\Framework\TestCase;
use App\Catalog;

class CatalogTest extends TestCase
{
    private $katalog;
    private $testFile = __DIR__ . '/test_products.json';

    protected function setUp(): void
    {
        $dummyData = [
            "PRD-1" => ["nama" => "Kemeja Flanel", "harga" => 150000, "stok" => 10],
            "PRD-2" => ["nama" => "Celana Jeans", "harga" => 250000, "stok" => 5],
        ];
        file_put_contents($this->testFile, json_encode($dummyData));
        $this->katalog = new Catalog($this->testFile);
    }

    // UT-01: Pencarian dengan keyword valid
    public function testSearchProductFound()
    {
        $result = $this->katalog->searchProduct("Kemeja");
        $this->assertCount(1, $result);
    }

    // UT-02: Pencarian dengan keyword kosong
    public function testSearchProductEmpty()
    {
        $result = $this->katalog->searchProduct("");
        $this->assertNotEmpty($result);
    }

    protected function tearDown(): void
    {
        unlink($this->testFile);
    }
}