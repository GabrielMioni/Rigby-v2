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

        echo 'ProductId: ' .$result;
    }



}