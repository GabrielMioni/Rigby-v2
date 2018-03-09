<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Review;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

class ReviewFixture extends Fixture
{
    protected $faker;

    public function __construct()
    {
        $this->faker = Faker\Factory::create();
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $reviewsRequested = 300;

        $startTime = '2018/02/01';

        $reviewDates = $this->makeReviewDates($reviewsRequested, 7, $startTime);

        foreach ($reviewDates as $date)
        {
            $review = new Review();

            $nameAndEmail = $this->randomNameAndEmail();

            $review->setRating(rand(1,5));
            $review->setContent( $this->randomContent() );
            $review->setEmail( $nameAndEmail['email'] );
            $review->setName( $nameAndEmail['name'] );
            $review->setProduct( $this->randomProduct() );
            $review->setCreated( new \DateTime( date('Y-m-d H:i:s', strtotime($date) ) ) );
            $review->setUpdated( new \DateTime('now'));
            $review->setTitle( $this->randomTitle() );

            $manager->persist($review);
            $manager->flush();
        }
    }

    protected function makeReviewDates($reviewsRequested, $days, $dateStart)
    {
        $rough = ceil($reviewsRequested / $days);
        $unixStart = strtotime($dateStart);

        $day_array = array();

        while (count($day_array) < $days)
        {
            $modifyNum = rand(0, $rough/2);
            $day_array[date('Y-m-d', $unixStart)] = rand(0,1) === 0 ? $rough - $modifyNum : $rough + $modifyNum;
            $unixStart += 86400;
        }

        $reviewDates = array();

        foreach ($day_array as $dateKey=>$reviewCount)
        {
            $x = 0;

            while ($x < $reviewCount)
            {
                $reviewDates[] = $this->setRandomTime($dateKey);

                ++$x;
            }
        }

        usort($reviewDates, array($this, 'date_sort'));

        $firstDate = $reviewDates[0];
        $lastDate  = $reviewDates[count($reviewDates) - 1];

        while (count($reviewDates) < $reviewsRequested)
        {
            $reviewDates[] = $this->setRandomTime($firstDate, $lastDate);
        }

        while (count($reviewDates) > $reviewsRequested)
        {
            unset( $reviewDates[ rand(0, count($reviewDates) - 1) ] );
        }

        usort($reviewDates, array($this, 'date_sort'));

        return $reviewDates;
    }

    protected function setRandomTime($date, $max = false)
    {
        $min = strtotime($date);
        $max = $max === false ? $min + 86400 - 1 : strtotime($max);

        $randomTime =  date('Y-m-d H:i:s', rand($min, $max));

        return $randomTime;
    }

    protected function date_sort($a, $b) {
        return strtotime($a) - strtotime($b);
    }

    protected function randomProduct()
    {
        $products = ['GOOP', 'DNGLS', 'SNACKS', 'FATBCK', 'BEER', 'CAT_FRNDZ', 'JNGLS', 'WEIRDZ'];
        
        return $products[rand(0, count($products)-1)];
    }

    protected function randomTitle()
    {
        return $this->faker->sentence();
    }

    protected function randomContent()
    {
        $paragrahCount = rand(1, 3);
        $content = $this->faker->paragraph($paragrahCount);

        return substr($content, 0, 600);
    }

    protected function randomNameAndEmail()
    {
        $firstName = $this->faker->firstName;
        $lastName  = $this->faker->lastName;
        $email = $firstName . '@' . $this->faker->domainName;

        return ['name' => $firstName . ' ' . $lastName, 'email' => $email];
    }
}