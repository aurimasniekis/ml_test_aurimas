<?php

namespace Aurimas\IssuesBundle\Model;

/**
 * Class IssueModel
 * @package Aurimas\IssuesBundle\Model
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class IssueModel extends BaseModel
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var string
     */
    protected $title;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function saveData()
    {
        $result = [];

        $result['body'] = $this->getBody();
        $result['title'] = $this->getTitle();

        return $result;
    }
}
