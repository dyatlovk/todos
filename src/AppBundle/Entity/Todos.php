<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Todos
 *
 * @ORM\Table(name="todos")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TodosRepository")
 */
class Todos
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", length=255)
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_create", type="datetime")
     */
    private $dateCreate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modify", type="datetime")
     */
    private $dateModify;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_sheduled", type="datetime")
     */
    private $dateSheduled;

    /**
     * @var int
     *
     * @ORM\Column(name="cat_id", type="integer")
     */
    private $catId;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="todos")
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @var int
     *
     * @ORM\Column(name="user", type="integer", length=3)
     */
    private $user;

    public function __construct()
    {
        $this->category = new ArrayCollection();
        $this->dateCreate = new \DateTime();
        $this->dateModify = new \DateTime();
        $this->dateSheduled = new \DateTime('now +1 month');
    }


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
     * Set title
     *
     * @param string $title
     *
     * @return Todos
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
     * Set content
     *
     * @param string $content
     *
     * @return Todos
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set dateCreate
     *
     * @param \DateTime $dateCreate
     *
     * @return Todos
     */
    public function setDateCreate($dateCreate)
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    /**
     * Get dateCreate
     *
     * @return \DateTime
     */
    public function getDateCreate()
    {
        return $this->dateCreate;
    }

    /**
     * Set dateModify
     *
     * @param \DateTime $dateModify
     *
     * @return Todos
     */
    public function setDateModify($dateModify)
    {
        $this->dateModify = $dateModify;

        return $this;
    }

    /**
     * Get dateModify
     *
     * @return \DateTime
     */
    public function getDateModify()
    {
        return $this->dateModify;
    }

    /**
     * Set dateSheduled
     *
     * @param \DateTime $dateSheduled
     *
     * @return Todos
     */
    public function setDateSheduled($dateSheduled)
    {
        $this->dateSheduled = $dateSheduled;

        return $this;
    }

    /**
     * Get dateSheduled
     *
     * @return \DateTime
     */
    public function getDateSheduled()
    {
        return $this->dateSheduled;
    }

    /**
     * Set catId
     *
     * @param integer $catId
     *
     * @return Todos
     */
    public function setCatId($catId)
    {
        $this->catId = $catId;

        return $this;
    }

    /**
     * Get catId
     *
     * @return int
     */
    public function getCatId()
    {
        return $this->catId;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Todos
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get Category
     * @return ArrayCollection
     */
    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get user
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     * @param int $id
     * @return Todos
     */
    public function setUser($id)
    {
        $this->user = $id;

        return $this;
    }
}

