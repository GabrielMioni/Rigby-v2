<?php

namespace Tests\AppBundle\Fixtures;

use AppBundle\DataFixtures\ORM\ProductFixture;
use PHPUnit_Framework_TestCase;

class ProductFixtureTest extends PHPUnit_Framework_TestCase
{
    protected $productFixtureObj;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->productFixtureObj = new ProductFixture();
    }

    public function testProductId()
    {
        $result = $this->productFixtureObj->randomProductId();

        echo 'ProductId: ' .$result . PHP_EOL;
    }

    public function testProductIdArray()
    {
        $idArray = $this->productFixtureObj->buildProductIds(100);
        
        echo "Ids";

        foreach ($idArray as $value)
        {
            echo $value . PHP_EOL;
        }

        echo "Total Ids: " . count($idArray) . PHP_EOL;
    }

    public function testProductNames()
    {
        $name = $this->productFixtureObj->randomProductName();

        echo "Name: $name " . PHP_EOL;
    }

    public function testProductNamesArray()
    {

    }
}