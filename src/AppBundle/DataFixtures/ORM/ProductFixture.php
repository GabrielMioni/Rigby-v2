<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

class ProductFixture extends Fixture
{
    protected $faker;

    public function __construct()
    {
        $this->faker = Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $productsRequested = 100;

        $productNames = $this->buildProductNames($productsRequested);
        $productIds   = $this->buildProductIds($productsRequested);

        foreach ($productNames as $key=>$value)
        {
            $productName = $value;
            $productId   = $productIds[$key];
            $price = $this->randomPrice();

            $product = new Product();

            $product->setDescription($productName);
            $product->setProductId($productId);
            $product->setPrice($price);

            $manager->persist($product);
            $manager->flush();
        }
    }

    protected function buildProductNames($productsRequested) {

        $names = [];

        while (count($names) < $productsRequested)
        {
            $randomName = $this->randomProductName();

            $occurrenceCount = $this->getOccurrenceCount($names, $randomName);

            $names[] = $occurrenceCount < 0 ? $randomName . ($occurrenceCount + 1) : $randomName;
        }

        return $names;
    }

    protected function buildProductIds($productsRequested) {
        $ids = [];

        while (count($ids) < $productsRequested)
        {
            while (! in_array($id = $this->randomProductKey(), $ids))
            {
                $ids[] = $id;
            }
        }

        return $ids;
    }


    protected function getOccurrenceCount(array $haystack, $needle) {
        $occurrenceCount = 0;

        foreach ($haystack as $key=>$value)
        {
            if (strpos($value, $needle) !== -1)
            {
                ++$occurrenceCount;
            }
        }

        return $occurrenceCount;
    }

    protected function randomProductName() {

        $rand = rand(0, 3);

        $word = '';

        switch ($rand)
        {
            case 0:
                $word = $this->faker->city;
                break;
            case 1:
                $word = $this->faker->company;
                break;
            case 2:
                $word = $this->faker->firstName;
                break;
            case 3:
                $word = $this->faker->country;
                break;
        }

        $expl = explode(' ', $word);

        $randWordIndex = rand(0, count($expl) -1);

        $productName = $expl[$randWordIndex];

        return $productName;
    }

    protected function randomProductKey() {

        $alphabets = range('A', 'Z');

        $partOne = '';
        $partTwo = '';

        for ($i = 0 ; $i < 4 ; ++$i)
        {
            $index = rand(0, count($alphabets) -1);

            $alph = $alphabets[$index];

            $partOne .= $alph;
            $partTwo .= rand(0,9);
        }


        $key = $partOne . '-' . $partTwo . rand(0,9);

        return $key;
    }

    protected function randomPrice() {

        $dollar = rand(5, 100);

        $change = [25, 50, 95, 00];

        $cents = $change[rand(0, count($change) -1)];

        return '$'. "$dollar.$cents";
    }
}