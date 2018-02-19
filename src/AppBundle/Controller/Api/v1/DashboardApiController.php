<?php

namespace AppBundle\Controller\Api\v1;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Review;

class DashboardApiController extends Controller
{

    /**
     * @Route("/Api/v1/weekly", name="weekly")
     */
    public function WeeklyAction()
    {

    }
}