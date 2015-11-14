<?php

namespace APP\AnswersBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Car
 *
 * @ORM\Entity(repositoryClass="APP\AnswersBundle\Entity\AnswerRepository")
 * @ORM\Table(name="cars",
 *      indexes={
 *          @ORM\Index(name="search_idx", columns={
 *              "fabrication_year",
 *              "producer"
 *          })
 *      }
 * )
 */
class Car
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
     * @var string
     *
     * @ORM\Column(name="fabrication_year", type="integer")
     */
    private $fabrication_year;

    /**
     * @var string
     *
     * @ORM\Column(name="producer", type="string", length=255)
     */
    private $producer;

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
     * Set fabricationYear
     *
     * @param integer $fabricationYear
     *
     * @return Car
     */
    public function setFabricationYear($fabricationYear)
    {
        $this->fabrication_year = $fabricationYear;

        return $this;
    }

    /**
     * Get fabricationYear
     *
     * @return integer
     */
    public function getFabricationYear()
    {
        return $this->fabrication_year;
    }

    /**
     * Set producer
     *
     * @param string $producer
     *
     * @return Car
     */
    public function setProducer($producer)
    {
        $this->producer = $producer;

        return $this;
    }

    /**
     * Get producer
     *
     * @return string
     */
    public function getProducer()
    {
        return $this->producer;
    }
}
