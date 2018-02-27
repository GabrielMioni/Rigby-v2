<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Review;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

    /**
     * @Route("/reviews", name="reviews")
     */
    public function ReviewsAction(Request $request)
    {
        $searchForm = $this->createReviewSearchForm();

        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted())
        {
            $data = $searchForm->getData();

            $generalSearch = $searchForm->get("search_reviews")->getData();

            $type = $searchForm->get("type")->getData();
            $operator = $searchForm->get("operator")->getData();
            $value = $searchForm->get("value")->getData();

            dump($type);
            dump($operator);
            dump($value);

            $filers = array();

            $em = $this->getDoctrine()->getManager();

            $searchResults = $em->getRepository('AppBundle:Review')->reviewSearch($filers, $generalSearch);
            $searchResults = json_decode($searchResults);

            dump($searchResults);

        }

        return $this->render('admin/reviews.html.twig', array('form'=>$searchForm->createView()));
    }

    protected function createReviewSearchForm()
    {
        $review = new Review();

        $searchForm = $this->createFormBuilder($review)
            ->add('search_reviews', TextType::class, array('label'=>'Search', 'required'=>false, "mapped" => false))
            ->add('type', CollectionType::class, array(
                'attr' => array('class' => false, 'id' => false),
                'mapped' => false,
                'label' => false,
                'prototype' => false,
                'allow_add' => true,
                'required'   => false,
                'entry_type'   => ChoiceType::class,
                'entry_options'  => array(
                    'choices'  => array(
                        'Title'     => 'title',
                        'Name'      => 'name',
                        'Email'     => 'email',
                        'Content'   => 'content',
                        'Product'   => 'product',
                        'Created'   => 'created',
                    ),
                ),
            ))
            ->add('operator', CollectionType::class, array(
                'mapped' => false,
                'label' => false,
                'prototype' => false,
                'allow_add' => true,
                'required'   => false,
                'entry_type'   => ChoiceType::class,
                'entry_options'  => array(
                    'choices'  => array(
                        'Contains'              => 1,
                        'Doesn\'nt Contain'     => 2,
                        'Equal'                 => 3,
                        'Not Equal'             => 4,
                        'Regular Expression'    => 5,
                    ),
                ),
            ))
            ->add('value', CollectionType::class, array(
                'mapped'        => false,
                'label'         => false,
                'prototype' => false,
                'allow_add'     => true,
                'required'      => false,
                'entry_type'    => TextType::class,
            ))
            ->add('search', SubmitType::class, array('label' => 'Submit Search'))
            ->getForm();

        return $searchForm;
    }

    /**
     * @Route("/ajaxAddCriteria", name="addCriteria")
     */
    public function ReviewFilterAddCriteriaAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();

        if ($isAjax === false)
        {
            die();
        }

        $postData = $request->request->get('contact');

        $number = $postData['number'];
        $criteria = $postData['criteria'];
        $operator = $postData['operator'];

        return $this->render('admin/reviews.search.criteria.html.twig', array($number, $criteria, $operator));
    }
}
