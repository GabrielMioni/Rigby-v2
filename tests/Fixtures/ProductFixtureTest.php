<?php

namespace Tests\AppBundle\Fixtures;

use AppBundle\DataFixtures\ORM\ProductFixture;
use PHPUnit_Framework_TestCase;


class ProductFixtureTest extends PHPUnit_Framework_TestCase
{

    public function testProductId()
    {
        $test = new ProductFixture();

        $result = $test->randomProductId();

        echo 'ProductId: ' .$result . PHP_EOL;
    }

    public function testProductIdArray()
    {
        $test = new ProductFixture();

        $idArray = $test->buildProductIds(100);
        
        echo "Ids";

        foreach ($idArray as $value)
        {
            echo $value . PHP_EOL;
        }

        echo "Total Ids: " . count($idArray) . PHP_EOL;
    }

}