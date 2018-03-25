<?php

namespace Tests\AppBundle\Fixtures;

use AppBundle\DataFixtures\ORM\ProductFixture;
use PHPUnit_Framework_TestCase;

class ProductFixtureTest extends PHPUnit_Framework_TestCase
{
    protected $productFixtureObj;
    protected $productsRequested;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->productFixtureObj = new ProductFixture();
        $this->productsRequested = 100;
    }

    public function testProductId()
    {
        $result = $this->productFixtureObj->randomProductId();

        echo 'ProductId: ' .$result . PHP_EOL;
    }

    public function testProductIdArray()
    {
        $idArray = $this->productFixtureObj->buildProductIds($this->productsRequested);
        
        echo "Ids";

        $this->printArray($idArray);

        echo "Total Ids: " . count($idArray) . PHP_EOL;
    }

    public function testProductNames()
    {
        $name = $this->productFixtureObj->randomProductName();

        echo "Name: $name " . PHP_EOL . PHP_EOL;
    }

    public function testProductNamesArray()
    {
        $namesArray = $this->productFixtureObj->buildProductNames($this->productsRequested);

        echo 'Names Array: ' . PHP_EOL;

        $this->printArray($namesArray);
    }

    protected function printArray(array $array)
    {
        foreach ($array as $value)
        {
            echo $value . PHP_EOL;
        }
    }
}