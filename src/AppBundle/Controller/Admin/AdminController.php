<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Review;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    /**
     * @Route("/dashboard", name="dash")
     */
    public function DashboardAction()
    {
        $em = $this->getDoctrine()->getManager();

        $weeklyCount = $em->getRepository('AppBundle:Review')->getWeeklyData(date('m/d/y', strtotime('2/01/18')));
        $weeklyStats = $em->getRepository('AppBundle:Review')->getWeeklyStats(date('m/d/y', strtotime('2/01/18')));
        $recentReviews = $em->getRepository('AppBundle:Review')->getRecentReviews();

        $weeklyCount = json_decode($weeklyCount, true);
        $weeklyStats   = json_decode($weeklyStats, true);
        $recentReviews = json_decode($recentReviews, true);

        dump($recentReviews);

        if (isset($weeklyCount['legend']))
        {
            $weeklyLegend = $weeklyCount['legend'];
            unset($weeklyCount['legend']);
        } else {
            $weeklyLegend = array(10,8,6,4,2,0);
        }

        unset($weeklyCount['legend']);


        return $this->render('admin/dashboard.html.twig',
                                array(  'weeklyCount'=>$weeklyCount,
                                        'weeklyLegend'=>$weeklyLegend,
                                        'weeklyStats'=>$weeklyStats,
                                        'recentReviews'=>$recentReviews
                                ));
    }

}



/*
        echo '<pre>';
        print_r($weeklyAvg);
        echo '</pre>';

        echo '<pre>';
        print_r($recentReviews);
        echo '</pre>';
 */