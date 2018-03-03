<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Review;
use AppBundle\Form\Type\ReviewSearchType;
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
     * @Route("/reviews/{page}/{perPage}", name="reviews", requirements={
     *     "page"="\d+",
     *     "perPage"="\d+",
     * })
     */
    public function ReviewsAction(Request $request, $page = 1, $perPage = 10)
    {
        $searchForm = $this->createReviewSearchForm();
        $searchForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $generalSearch = null;
        $filterArray   = array();

        if ($searchForm->isSubmitted())
        {
            $generalSearch  = $searchForm->get("search_reviews")->getData();
            $type           = $searchForm->get("type")->getData();
            $operator       = $searchForm->get("operator")->getData();
            $value          = $searchForm->get("value")->getData();

            $filterArray = $this->setFilterArray($type, $operator, $value);
        }

        $reviewPagination = $em->getRepository('AppBundle:Review')->reviewSearch($filterArray, $generalSearch, $page, $perPage);
        dump($reviewPagination);

        $totalReviewsReturned = $reviewPagination->getIterator()->count();
        $reviews = $reviewPagination->getIterator();
        dump($reviews);

        $count = count($reviewPagination);

        $maxPages = ceil($reviewPagination->count() / $perPage);
        $thisPage = $page;


        return $this->render('admin/reviews.html.twig', array('form'=>$searchForm->createView()));
    }

    protected function createReviewSearchForm($isCreated = false)
    {
        $review = new Review();

        $operatorOption1 = $isCreated === false ? 'Contains' : 'Greater than';
        $operatorOption2 = $isCreated === false ? 'Doesn\'t contain' : 'Lesser than';

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
                        'Type' => array(
                            'Title'     => 'title',
                            'Name'      => 'name',
                            'Email'     => 'email',
                            'Content'   => 'content',
                            'Product'   => 'product',
                            'Created'   => 'created',
                        )
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
                        'Operator' => array(
                            $operatorOption1        => 1,
                            $operatorOption2        => 2,
                            'Equal'                 => 3,
                            'Not Equal'             => 4,
                            'Regular Expression'    => 5,
                        )
                    ),
                ),
            ))
            ->add('value', CollectionType::class, array(
                'mapped'        => false,
                'label'         => false,
                'prototype'     => false,
                'allow_add'     => true,
                'required'      => false,
                'entry_type'    => TextType::class,
            ))
            ->add('search', SubmitType::class, array('label' => 'Submit Search'))
            ->getForm();

        return $searchForm;
    }

    protected function setFilterArray(array $type, array $operator, array $value)
    {
        $max = max([count($type), count($operator), count($value)]);

        $filterArray = array();

        while (count($filterArray) < $max)
        {
            $currentIndex = count($filterArray);

            $t = isset($type[$currentIndex])? $type[$currentIndex] : null;
            $o = isset($operator[$currentIndex]) ? $operator[$currentIndex] : null;
            $v = isset($value[$currentIndex]) ? $value[$currentIndex] : null;

            $filterArray[] = ['type'=>$t, 'operator'=>$o, 'value'=>$v];
        }

        return $filterArray;
    }

    /**
     * @Route("/ajaxAddFilterInput", name="addCriteria")
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
