<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Review;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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

    /**
     * @Route("/reviews", name="reviews")
     */
    public function ReviewsAction()
    {
        $review = new Review();

        $searchForm = $this->createFormBuilder($review)
                            ->add('id', IntegerType::class, array('required'=>false))
                            ->add('rating', IntegerType::class, array('required'=>false))
                            ->add('title', TextType::class, array('required'=>false))
                            ->add('reviewer_name', TextType::class, array('label'=>'Name', 'required'=>false))
                            ->add('reviewer_email', TextType::class, array('label'=>'Email', 'required'=>false))
                            ->add('search', SubmitType::class, array('label' => 'search'))
                            ->getForm();

        return $this->render('admin/reviews.html.twig', array('form'=>$searchForm->createView()));
    }

}
