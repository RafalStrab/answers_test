<?php

namespace APP\AnswersBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * MostSearchedAnswer
 *
 * @ORM\Table(name="most_searched_answers")
 * @ORM\Entity(repositoryClass="APP\AnswersBundle\Entity\MostSearchedAnswerRepository")
 */
class MostSearchedAnswer
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Answer")
     * @ORM\JoinColumn(name="answer", referencedColumnName="id")
     */
    private $answer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return MostSearchedAnswer
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set answer
     *
     * @param \APP\AnswersBundle\Entity\Answer $answer
     *
     * @return MostSearchedAnswer
     */
    public function setAnswer(\APP\AnswersBundle\Entity\Answer $answer = null)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer
     *
     * @return \APP\AnswersBundle\Entity\Answer
     */
    public function getAnswer()
    {
        return $this->answer;
    }
}
