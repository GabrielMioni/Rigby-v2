<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Review
 *
 * @ORM\Table(name="review")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReviewRepository")
 */
class Review
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="rating", type="integer")
     */
    private $rating;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="reviewer_name", type="string", length=255, nullable=true)
     */
    private $reviewerName;

    /**
     * @var string
     *
     * @ORM\Column(name="reviewer_email", type="string", length=255, nullable=true)
     */
    private $reviewerEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="review_content", type="string", length=255, nullable=true)
     */
    private $reviewContent;

    /**
     * @var string
     *
     * @ORM\Column(name="review_product", type="string", length=255, nullable=true)
     */
    private $reviewProduct;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }



    /**
     * @var bool
     *
     * @ORM\Column(name="purchase_confirmed", type="boolean", nullable=true)
     */
    private $purchaseConfirmed;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set rating
     *
     * @param integer $rating
     *
     * @return Review
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Review
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set reviewerName
     *
     * @param string $reviewerName
     *
     * @return Review
     */
    public function setReviewerName($reviewerName)
    {
        $this->reviewerName = $reviewerName;

        return $this;
    }

    /**
     * Get reviewerName
     *
     * @return string
     */
    public function getReviewerName()
    {
        return $this->reviewerName;
    }

    /**
     * Set reviewerEmail
     *
     * @param string $reviewerEmail
     *
     * @return Review
     */
    public function setReviewerEmail($reviewerEmail)
    {
        $this->reviewerEmail = $reviewerEmail;

        return $this;
    }

    /**
     * Get reviewerEmail
     *
     * @return string
     */
    public function getReviewerEmail()
    {
        return $this->reviewerEmail;
    }

    /**
     * Set reviewContent
     *
     * @param string $reviewContent
     *
     * @return Review
     */
    public function setReviewContent($reviewContent)
    {
        $this->reviewContent = $reviewContent;

        return $this;
    }

    /**
     * Get reviewContent
     *
     * @return string
     */
    public function getReviewContent()
    {
        return $this->reviewContent;
    }

    /**
     * Set reviewProduct
     *
     * @param string $reviewProduct
     *
     * @return Review
     */
    public function setReviewProduct($reviewProduct)
    {
        $this->reviewProduct = $reviewProduct;

        return $this;
    }

    /**
     * Get reviewProduct
     *
     * @return string
     */
    public function getReviewProduct()
    {
        return $this->reviewProduct;
    }

    /**
     * Set purchaseConfirmed
     *
     * @param boolean $purchaseConfirmed
     *
     * @return Review
     */
    public function setPurchaseConfirmed($purchaseConfirmed)
    {
        $this->purchaseConfirmed = $purchaseConfirmed;

        return $this;
    }

    /**
     * Get purchaseConfirmed
     *
     * @return bool
     */
    public function getPurchaseConfirmed()
    {
        return $this->purchaseConfirmed;
    }

}

