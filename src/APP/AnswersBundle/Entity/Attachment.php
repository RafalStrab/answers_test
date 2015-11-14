<?php

namespace APP\AnswersBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Attachment
 *
 * @ORM\Table(name="attachments")
 * @ORM\Entity(repositoryClass="APP\AnswersBundle\Entity\AttachmentRepository")
 */
class Attachment
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
     * @ORM\Column(name="original_filename", type="string", length=255)
     */
    private $original_filename;

    /**
     * @var string
     *
     * @ORM\Column(name="system_filename", type="string", length=255)
     */
    private $system_filename;

    /**
     * @var string
     *
     * @ORM\Column(name="system_path", type="string", length=255)
     */
    private $system_path;

    /**
     * @var string
     *
     * @ORM\Column(name="mime_type", type="string", length=255, nullable=true)
     */
    private $mime_type;

    /**
     * @var string
     *
     * @ORM\Column(name="extension", type="string", length=5, nullable=true)
     */
    private $extension;

    /**
     * @var integer
     *
     * @ORM\Column(name="size", type="integer", nullable=true)
     */
    private $size;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Answer")
     * @ORM\JoinColumn(name="answer", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $answer;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Comment")
     * @ORM\JoinColumn(name="comment", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $comment;

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
     * Set originalFilename
     *
     * @param string $originalFilename
     *
     * @return Attachment
     */
    public function setOriginalFilename($originalFilename)
    {
        $this->original_filename = $originalFilename;

        return $this;
    }

    /**
     * Get originalFilename
     *
     * @return string
     */
    public function getOriginalFilename()
    {
        return $this->original_filename;
    }

    /**
     * Set systemFilename
     *
     * @param string $systemFilename
     *
     * @return Attachment
     */
    public function setSystemFilename($systemFilename)
    {
        $this->system_filename = $systemFilename;

        return $this;
    }

    /**
     * Get systemFilename
     *
     * @return string
     */
    public function getSystemFilename()
    {
        return $this->system_filename;
    }

    /**
     * Set systemPath
     *
     * @param string $systemPath
     *
     * @return Attachment
     */
    public function setSystemPath($systemPath)
    {
        $this->system_path = $systemPath;

        return $this;
    }

    /**
     * Get systemPath
     *
     * @return string
     */
    public function getSystemPath()
    {
        return $this->system_path;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     *
     * @return Attachment
     */
    public function setMimeType($mimeType)
    {
        $this->mime_type = $mimeType;

        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mime_type;
    }

    /**
     * Set extension
     *
     * @param string $extension
     *
     * @return Attachment
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set size
     *
     * @param integer $size
     *
     * @return Attachment
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Attachment
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
     * @return Attachment
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

    /**
     * Set comment
     *
     * @param \APP\AnswersBundle\Entity\Comment $comment
     *
     * @return Attachment
     */
    public function setComment(\APP\AnswersBundle\Entity\Comment $comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return \APP\AnswersBundle\Entity\Comment
     */
    public function getComment()
    {
        return $this->comment;
    }
}
