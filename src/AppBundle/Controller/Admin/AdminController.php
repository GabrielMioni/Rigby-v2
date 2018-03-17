<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Review;
use Faker\Provider\cs_CZ\DateTime;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;


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

        $session = new Session();

        if ($searchForm->isSubmitted())
        {
            $generalSearch  = $searchForm->get("search_reviews")->getData();
            $type           = $searchForm->get("type")->getData();
            $operator       = $searchForm->get("operator")->getData();
            $value          = $searchForm->get("value")->getData();

            $session->set('generalSearch', $generalSearch);
            $session->set('type', $type);
            $session->set('operator', $operator);
            $session->set('value', $value);

            return $this->redirectToRoute('reviews');
        }

        $generalSearch  = $this->setFromSessionBag($session, 'generalSearch');
        $type           = $this->setFromSessionBag($session, 'type');
        $operator       = $this->setFromSessionBag($session, 'operator');
        $value          = $this->setFromSessionBag($session, 'value');

        $searchForm->get('search_reviews')->setData($generalSearch);
        $searchForm->get('type')->setData($type);
        $searchForm->get('operator')->setData($operator);
        $searchForm->get('value')->setData($value);

        $em = $this->getDoctrine()->getManager();

        $filterArray = ($type !== null && $operator !== null && $value !== null) ? $this->setFilterArray($type, $operator, $value) : array();

        $reviewPagination = $em->getRepository('AppBundle:Review')->reviewSearch($filterArray, $generalSearch, $page, $perPage);
        $reviews = $reviewPagination->getIterator();

        $updateForms = $this->createReviewUpdateForms($reviews);

        $totalReviews = count($reviewPagination);

        $url = $this->generateUrl('reviews');

        $pagination = $this->buildPaginationNavData($totalReviews, $page, $perPage, $url);

        return $this->render('admin/reviews.html.twig', array(
            'form'=>$searchForm->createView(),
            'reviews'=>$reviews,
            'pagination'=>$pagination,
            'updateForms'=>$updateForms
        ));
    }

    protected function setFromSessionBag(Session $session, $index)
    {
        return $session->has($index) === true ? $session->get($index) : null;
    }

    protected function buildPaginationNavData($totalReviews, $page, $perPage, $url)
    {
        $maxPages = ceil($totalReviews / $perPage);
        $page = intval($page);

        if ($maxPages <= 1)
        {
            return array();
        }

        $paginationArray = array();

        $url1 = $url . '/1/' . $perPage;
        $url2 = $page > 1 ? $url . '/' . ($page -1) . "/$perPage" : $url . '/1/' . $perPage;
        $navLeftStatus = $page === 1 ? 'disabled' : false;

        $pointer = $page > 5 ? $page - 5 : 1;

        $paginationArray[] = array('url'=>$url1, 'page'=>'<i class="fas fa-angle-double-left"></i>', 'status'=>$navLeftStatus);
        $paginationArray[] = array('url'=>$url2, 'page'=>'<i class="fas fa-angle-left"></i>', 'status'=>$navLeftStatus);

//        while (count($paginationArray) < 11 && $pointer <= $maxPages)
        while (count($paginationArray) < 13 && $pointer <= $maxPages)
        {
            $buttonStatus = $pointer === $page ? 'active' : false;
            $paginationArray[] = array('url'=>"$url/$pointer/$perPage", 'page'=>$pointer, 'status'=>$buttonStatus);
            ++$pointer;
        }

        $navRightStatus = $page === intval($maxPages) ? 'disabled' : false;

        $url3 = $page < $maxPages ? $url . '/' . ($page +1) . "/$perPage" : "$url/$maxPages/$perPage";
        $url4 = "$url/$maxPages/$perPage";

        $paginationArray[] = array('url'=>$url3, 'page'=>'<i class="fas fa-angle-right"></i>', 'status'=>$navRightStatus);
        $paginationArray[] = array('url'=>$url4, 'page'=>'<i class="fas fa-angle-double-right"></i>', 'status'=>$navRightStatus);

        return $paginationArray;
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

    protected function createReviewUpdateForms(\ArrayIterator $reviews)
    {
        $reviewUpdateFormViews = array();

        foreach ($reviews as $r)
        {
            $id      = $r->getId();
            $title   = $r->getTitle();
            $content = $r->getContent();
            $rating  = $r->getRating();
            $name    = $r->getName();
            $email   = $r->getEmail();
            $created = $r->getCreated();

            $review = new Review();
            $review->setCreated($created);
            $review->setTitle($title);
            $review->setContent($content);
            $review->setRating($rating);
            $review->setName($name);
            $review->setEmail($email);

            $formBuilder = $this->createFormBuilder($review)
                ->add('id', HiddenType::class, array(
                    'mapped' => false,
                    'label' => false,
                    'data' => $id,
                ))
                ->add('created', HiddenType::class, array(
                    'mapped' => false,
                    'label' => false,
                ))
                ->add('title', TextType::class)
                ->add('name', TextType::class)
                ->add('email', EmailType::class)
                ->add('content', TextareaType::class)
                ->add('rating', ChoiceType::class, array(
                    'choices'  => array(
                        '5' => 5,
                        '4' => 4,
                        '3' => 3,
                        '2' => 2,
                        '1' => 1,
                    ),
                ))
                ->add('save', SubmitType::class, array('label' => 'Update'));

            $formView = $formBuilder->getForm()->createView();

            $reviewUpdateFormViews[] = $formView;
        }

        return $reviewUpdateFormViews;
    }

    function createUpdateForm() {
        $review = new Review();

        $formBuilder = $this->createFormBuilder($review)
            ->add('id', HiddenType::class, array(
                'mapped' => false,
                'label' => false,
            ))
            ->add('created', HiddenType::class, array(
                'mapped' => false,
                'label' => false,
            ))
            ->add('title', TextType::class)
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('content', TextareaType::class)
            ->add('rating', ChoiceType::class, array(
                'choices'  => array(
                    '5' => 5,
                    '4' => 4,
                    '3' => 3,
                    '2' => 2,
                    '1' => 1,
                ),
            ))
            ->add('save', SubmitType::class, array('label' => 'Update'));

        return $formBuilder;
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

    /**
     * @Route("/ajaxReviewUpdate", name="updateReview")
     */
    public function AjaxReviewUpdateAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();

        if ($isAjax === false)
        {
            die();
        }

        $review = new Review();

        $formBuilder = $this->createFormBuilder($review)
            ->add('id', HiddenType::class, array(
                'mapped' => false,
                'label' => false,
            ))
            ->add('created', HiddenType::class, array(
                'mapped' => false,
                'label' => false,
            ))
            ->add('title', TextType::class)
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('content', TextareaType::class)
            ->add('rating', ChoiceType::class, array(
                'choices'  => array(
                    '5' => 5,
                    '4' => 4,
                    '3' => 3,
                    '2' => 2,
                    '1' => 1,
                ),
            ))
            ->add('save', SubmitType::class, array('label' => 'Update'));

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $id      = $form["id"]->getData();
            $title   = $form['title']->getData();
            $name    = $form['name']->getData();
            $email   = $form['email']->getData();
            $content = $form['content']->getData();
            $rating  = $form['rating']->getData();

            $reviewDataArray = ['id'=>$id, 'title'=>$title, 'name'=>$name, 'email'=>$email, 'content'=>$content, 'rating'=>$rating];

            $em = $this->getDoctrine()->getManager();

            $reviewBeforeUpdate = $em->getRepository('AppBundle:Review')->getReviewById($id);

            if (
                $reviewBeforeUpdate['title']   === $title &&
                $reviewBeforeUpdate['name']    === $name &&
                $reviewBeforeUpdate['email']   === $email &&
                $reviewBeforeUpdate['content'] === $content &&
                $reviewBeforeUpdate['rating']  === $rating
            ) {
                $status = 'noDiff';
            } else {
                $update = $em->getRepository('AppBundle:Review')->updateReview($reviewDataArray);

                $status = $update;
            }

        } else{
             $status = "invalid";
        }

        return new JsonResponse(array('status' => $status));
    }

    /**
     * @Route("/ajaxGetReviewById", name="getReviewById")
     */
    public function AjaxGetReviewByIdAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('id');

        $result = $em->getRepository('AppBundle:Review')->getReviewById($id);

        return new JsonResponse(array($result));
    }

    /**
     * @Route("/reviewSubmit", name="reviewSubmit")
     */
    public function reviewSubmitAction(Request $request)
    {
        $formBuilder = $this->createUpdateForm();
        $reviewForm = $formBuilder->getForm();

        $reviewForm->handleRequest($request);

        $noGo = null;
        $thankYou = null;

        if ($reviewForm->isSubmitted() && $reviewForm->isValid() )
        {
            $ip = $request->getClientIp();

            $em = $this->getDoctrine()->getManager();

            $ipResult = $em->getRepository('AppBundle:Review')->getReviewByIp($ip);

            $mins = count($ipResult) > 0 ? $minsSinceLastSubmit = ( time() - strtotime($ipResult[0]) ) / 60 : null;

            $noGo = $mins !== null & $mins < 5 ? 'tooSoon' : null;

            if ($noGo === null)
            {
                $rating = $reviewForm['rating']->getData();
                $name  = $reviewForm['name']->getData();
                $email = $reviewForm['email']->getData();
                $title = $reviewForm['title']->getData();
                $content = $reviewForm['content']->getData();

                $dateTimeObj = new \DateTime('now');

                $newReview = new Review();
                $newReview->setRating($rating);
                $newReview->setName($name);
                $newReview->setEmail($email);
                $newReview->setTitle($title);
                $newReview->setContent($content);
                $newReview->setCreated($dateTimeObj);
                $newReview->setUpdated($dateTimeObj);
                $newReview->setIp($ip);

                $em->persist($newReview);
                $em->flush();
                $thankYou = true;
            }

        }

        return $this->render('public/review-submit.html.twig', array(
            'noGo'=>$noGo,
            'thankYou'=>$thankYou,
            'form'=>$reviewForm->createView()
        ));
    }
}
