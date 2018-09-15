<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 19/05/2018
 * Time: 20:18
 */

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BoardRepository")
 * @ORM\Table(name="board")
 */
class Board
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @ORM\Column(type="string", columnDefinition="text", name="`text`")
     */
    private $text;

    /**
     * @ORM\ManyToOne(targetEntity="Character")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $hasVeto = false;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="veto_author_id", referencedColumnName="id")
     */
    private $vetoAuthor = null;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $isCrypted = false;
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Board constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return bool
     */
    public function isHasVeto()
    {
        return $this->hasVeto;
    }

    /**
     * @param bool $hasVeto
     */
    public function setHasVeto(bool $hasVeto)
    {
        $this->hasVeto = $hasVeto;
    }

    /**
     * @return bool
     */
    public function isCrypted()
    {
        return $this->isCrypted;
    }

    /**
     * @param bool $isCrypted
     */
    public function setIsCrypted(bool $isCrypted)
    {
        $this->isCrypted = $isCrypted;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getVetoAuthor()
    {
        return $this->vetoAuthor;
    }

    /**
     * @param mixed $vetoAuthor
     */
    public function setVetoAuthor($vetoAuthor): void
    {
        $this->vetoAuthor = $vetoAuthor;
    }

}