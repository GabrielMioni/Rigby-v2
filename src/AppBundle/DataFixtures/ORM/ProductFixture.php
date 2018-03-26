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
        $productsRequested = 10;

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

    public function buildProductNames($productsRequested) {

        $names = [];

        while (count($names) < $productsRequested)
        {
            $names[] = $this->randomProductName();
        }

        return $names;
    }

    public function buildProductIds($productsRequested) {
        $ids = [];

        for ($i = 0 ; $i < $productsRequested ; ++$i)
        {
            $ids[] = $this->randomProductId();
        }

        while (count(array_unique($ids)) !== $productsRequested)
        {
            $uniqueCount = count(array_unique($ids));
            $needed = $productsRequested - $uniqueCount;

            $ids = array_unique($ids);

            for ($x = 0 ; $x < $needed ; ++$x)
            {
                $ids[] = $this->randomProductId();
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

    public function randomProductName() {

        $word = '';

        while (strlen($word) < 4)
        {
            $rand = rand(0, 4);

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
                    $word = $this->faker->colorName;
                    break;
                case 4:
                    $word = $this->faker->monthName;
                    break;
            }

            if (strpos($word, '-') !== -1)
            {
                $word = substr($word, 0, strpos($word, '-'));
            }

            $word = preg_replace('/[.,]/', '', $word);

            $wordParts = explode(' ', $word);

            $randWordIndex = rand(0, count($wordParts) -1);

            $word = $wordParts[$randWordIndex];
        }

        return $word;
    }

    public function randomProductId() {

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

    public function randomPrice() {

        $dollar = rand(5, 100);

        $change = [25, 50, 95, 00];

        $cents = $change[rand(0, count($change) -1)];

        return "$dollar.$cents";
    }
}