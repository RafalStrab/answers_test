<?php

namespace APP\AnswersBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Cocur\Slugify\Slugify;
use APP\AnswersBundle\Entity\Answer;
use APP\AnswersBundle\Entity\Comment;
use APP\AnswersBundle\Entity\Attachment;
use APP\AnswersBundle\Entity\MostSearchedAnswer;

/**
 * API Controller
 *
 * @Route("/api")
 */
class APIController extends Controller
{
    /**
     * @ApiDoc(
     *  description="Create new answer.",
     *  tags={"JSON"}
     * )
     * 
     * @Route("/create-answer", name="api_create_answer", options={"expose"=true})
     * @Method("POST")
     */
    public function createAnswerAction(Request $request)
    {
        $answerTitle         = $request->request->get('answerTitle');
        $answerDescription   = $request->request->get('answerDescription');
        $answerAuthor        = $request->request->get('answerAuthor');
        $uploadedAttachment  = $request->files->get('file');

        $answerSlug = $this->getSlug($answerTitle);
        $answerUri = $this->generateUrl('api_get_answer', array('answerSlug' => $answerSlug), true);

        $em = $this->getDoctrine()->getManager();

        try {
        	$answer = new Answer();
	        $answer
	        	->setTitle($answerTitle)
                ->setDescription($answerDescription)
                ->setUri($answerUri)
	        	->setSlug($answerSlug)
	        	->setCreatedBy($answerAuthor)
	        ;
	        $em->persist($answer);

            if ($uploadedAttachment) {
                $attachment = $this->setAttachment($uploadedAttachment[0], $answer, 'answer');

                $em->persist($attachment);
                
                $this->moveFile($uploadedAttachment[0], $attachment->getSystemPath(), $attachment->getSystemFileName());
            }

            $em->flush();


			$success  = true;
			$answerUri = $answer->getUri();
        } catch (Exception $e) {
        	$success = false;
        	$answerUri = null;
        }

        return new JsonResponse(
            array(
            	'success'   => $success,
            	'answerUri' => $answerUri
            )
        );
    }

    /**
     * @ApiDoc(
     *  description="Create new answer.",
     *  tags={"JSON"}
     * )
     * 
     * @Route("/create-comment", name="api_create_comment", options={"expose"=true})
     * @Method("POST")
     */
    public function createCommentAction(Request $request)
    {
        $answerId            = $request->request->get('answerId');
        $commentText         = $request->request->get('commentText');
        $commentAuthor       = $request->request->get('commentAuthor');
        $uploadedAttachment  = $request->files->get('file');

        $em = $this->getDoctrine()->getManager();

        $answer = $em->getRepository('APPAnswersBundle:Answer')->find($answerId);

        if (!$answer) {
            throw $this->createNotFoundException('Sorry not existing service.');
        }

        try {
            $comment = new Comment();
            $comment
                ->setText($commentText)
                ->setCreatedBy($commentAuthor)
                ->setAnswer($answer)
            ;
            $em->persist($comment);

            if ($uploadedAttachment) {
                $attachment = $this->setAttachment($uploadedAttachment[0], $comment, 'comment');

                $em->persist($attachment);                
                
                $this->moveFile($uploadedAttachment[0], $attachment->getSystemPath(), $attachment->getSystemFileName());
            }

            $em->flush();

            $comments = $em->getRepository('APPAnswersBundle:Comment')->findBy(array('answer' => $answer), array('createdAt' => 'DESC'));
            
            $arrComments = array();
            foreach ($comments as $key => $comment) {
                $arrComment['text'] = $comment->getText();
                $arrComment['created_by'] = $comment->getCreatedBy();
                $arrComment['created_at'] = $comment->getCreatedAt()->format("F d, Y");
                $arrComment['files']      = $this->getCommentAttachments($comment);

                array_push($arrComments, $arrComment);
            }

            $success  = true;
        } catch (Exception $e) {
            $success = false;
        }

        return new JsonResponse(
            array(
                'success'  => $success,
                'comments' => $arrComments
            )
        );
    }

    private function setAttachment($attachment, $parent, $parentType)
    {
        $newFileName = $this->getNewFilename($attachment);
        $systemPath  = $this->getSystemPath();

        $setAttachment = new Attachment();
        $setAttachment
            ->setOriginalFileName($attachment->getClientOriginalName())
            ->setSystemFileName($newFileName)
            ->setSystemPath($systemPath)
            ->setMimeType($attachment->getMimeType())
            ->setExtension($attachment->getClientOriginalExtension())
            ->setSize($attachment->getClientSize())
        ;

        if ($parent == 'comment') {
            
        }

        switch ($parentType) {
            case 'comment':
                $setAttachment->setComment($parent);
                break;
            case 'answer':
                $setAttachment->setAnswer($parent);
                break;
        }

        return $setAttachment;
    }

    private function getCommentAttachments($comment)
    {
        $em = $this->getDoctrine()->getManager();

        $commentAttachments = $em->getRepository('APPAnswersBundle:Attachment')->findByComment($comment);
        $arrAttachments = array();        
        if ($commentAttachments) {
            foreach ($commentAttachments as $key => $attachment) {
                $fileURI = $this->generateUrl('index_attachment_download', array('id' => $attachment->getId()), true);
                $arrAttachment['fileName'] = $attachment->getOriginalFilename();
                $arrAttachment['fileURI']  = $fileURI;

                array_push($arrAttachments, $arrAttachment);
            }
        }

        return $arrAttachments;
    }

    private function addToMostSearched($answer)
    {
        $em = $this->getDoctrine()->getManager();

        try {
            $mostSearchedAnswer = new MostSearchedAnswer();
            $mostSearchedAnswer
                ->setAnswer($answer)
            ;
            $em->persist($mostSearchedAnswer);
            $em->flush();

            return true;
        } catch (Exception $e) {
            return false;
        }   
    }

    /**
     * @ApiDoc(
     *  description="Get answer.",
     *  tags={"JSON"},
     *  requirements={
     *      {
     *          "name"="answerSlug",
     *          "dataType"="string"
     *      }
     *  },
     *  parameters={
     *      {"name"="answerSlug", "dataType"="string", "required"=true}
     *  }
     * )
     * @Route("/get-answer/{answerSlug}", name="api_get_answer", options={"expose"=true})
     * @Method({"GET"})
     */
    public function getAnswerAction($answerSlug)
    {
        $em = $this->getDoctrine()->getManager();
        
        $answer = $em->getRepository('APPAnswersBundle:Answer')->findBySlug($answerSlug);

        if (!$answer) {
            throw $this->createNotFoundException('Sorry not existing service.');
        }

        $comments = $em->getRepository('APPAnswersBundle:Comment')->findByAnswer($answer[0]);
        $answerAttachments = $em->getRepository('APPAnswersBundle:Attachment')->findByAnswer($answer[0]);

        $this->addToMostSearched($answer[0]);

        $arrAnswer['id'] = $answer[0]->getId();
        $arrAnswer['title'] = $answer[0]->getTitle();
        $arrAnswer['description'] = $answer[0]->getDescription();
        $arrAnswer['created_by']  = $answer[0]->getCreatedBy();
        $arrAnswer['created_at']  = $answer[0]->getCreatedAt()->format("F d, Y");

        $arrComments = array();
        foreach ($comments as $key => $comment) {
            $arrComment['text']       = $comment->getText();
            $arrComment['created_by'] = $comment->getCreatedBy();
            $arrComment['created_at'] = $comment->getCreatedAt()->format("F d, Y");
            $arrComment['files']      = $this->getCommentAttachments($comment);

            array_push($arrComments, $arrComment);
        }

        $arrAnswer['comments'] = $arrComments;

        $arrAttachments = array();
        foreach ($answerAttachments as $key => $attachment) {
            $fileURI = $this->generateUrl('index_attachment_download', array('id' => $attachment->getId()), true);
            $arrAttachment['fileName'] = $attachment->getOriginalFilename();
            $arrAttachment['fileURI']  = $fileURI;

            array_push($arrAttachments, $arrAttachment);
        }

        $arrAnswer['files'] = $arrAttachments;

        return new JsonResponse(
            $arrAnswer
        );
    }

     /**
     * @ApiDoc(
     *  description="Get comments.",
     *  tags={"JSON"},
     *  requirements={
     *      {
     *          "name"="answer",
     *          "dataType"="string"
     *      }
     *  },
     *  parameters={
     *      {"name"="answerSlug", "dataType"="string", "required"=true}
     *  }
     * )
     * @Route("/get-comments/{answerSlug}", name="api_get_comments", options={"expose"=true})
     * @Method({"GET"})
     */
    public function getCommentsAction($answerSlug)
    {
        $em = $this->getDoctrine()->getManager();

        $answer = $em->getRepository('APPAnswersBundle:Answer')->findBySlug($answerSlug);

        if (!$answer) {
            throw $this->createNotFoundException('Sorry not existing service.');
        }

        $comments = $em->getRepository('APPAnswersBundle:Comment')->findByAnswer($answer[0]);

        return new JsonResponse(
            $answer
        );
    }

    /**
     * @ApiDoc(
     *  description="Get all answers.",
     *  tags={"JSON"}
     * )
     * @Route("/get-all", name="api_get_all", options={"expose"=true})
     * @Method({"GET"})
     */
    public function getAllAction()
    {
        $em = $this->getDoctrine()->getManager();

        $answers = $em->getRepository('APPAnswersBundle:Answer')->findAll();

        $arrAnswers = array();
        foreach ($answers as $key => $value) {
            $arrAnswer['title'] = $value->getTitle();
            $arrAnswer['createdBy'] = $value->getCreatedBy();
            $arrAnswer['createdAt'] = $value->getCreatedAt()->format("F d, Y");
            
            $comments = $em->getRepository('APPAnswersBundle:Comment')->findByAnswer($value->getId());
            
            $arrAnswer['comments'] = count($comments);

            array_push($arrAnswers, $arrAnswer);
        }
        $a['data'] = $arrAnswers;

        return new JsonResponse(
            $a
        );
    }

    /**
     * @ApiDoc(
     *  description="Get newest answers (max 10).",
     *  tags={"JSON"}
     * )
     * @Route("/get-newest-answers", name="api_get_newest_answers", options={"expose"=true})
     * @Method({"GET"})
     */
    public function getNewestAnswersAction()
    {
        $em = $this->getDoctrine()->getManager();

        $conn = $em->getConnection();
        $statement = $conn->prepare("SELECT title, description, uri
                                     FROM answers
                                     ORDER BY created_at DESC
                                     LIMIT 0, 10");
        $statement->execute();
        $answers = $statement->fetchAll();

        return new JsonResponse(
            $answers
        );
    }

    /**
     * @ApiDoc(
     *  description="Get most searched answers (max 10).",
     *  tags={"JSON"}
     * )
     * @Route("/get-most-searched-answers", name="api_get_most_searched_answers", options={"expose"=true})
     * @Method({"GET"})
     */
    public function getMostSearchedAnswersAction()
    {
        $em = $this->getDoctrine()->getManager();

        $conn = $em->getConnection();
        $statement = $conn->prepare("SELECT answers.title, answers.description, answers.uri
                                    FROM most_searched_answers
                                    JOIN answers ON most_searched_answers.answer=answers.id
                                    GROUP BY answer
                                    ORDER BY COUNT(most_searched_answers.answer) DESC
                                    LIMIT 10;");
        $statement->execute();
        $answers = $statement->fetchAll();
        
        return new JsonResponse(
            $answers
        );
    }

    /**
     * @ApiDoc(
     *  description="Search for an answers.",
     *  tags={"JSON"},
     *  requirements={
     *      {
     *          "name"="query",
     *          "dataType"="string",
     *          "description"="searched string"
     *      }
     *  },
     *  parameters={
     *      {"name"="query", "dataType"="string", "required"=true, "description"="searched string"}
     *  }
     * )
     * @Route("/search", name="api_search_answer", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function searchAnswerAction(Request $request)
    {
        $requestMethod = strtolower($request->getMethod());

        if ($requestMethod == "post") {
        	$query = $request->request->get('query');
        } elseif ($requestMethod == 'get') {
        	$query = $request->query->get('query');
        }

        $em = $this->getDoctrine()->getManager();

        $conn = $em->getConnection();
		$statement = $conn->prepare("SELECT title, SUBSTRING(description, 1, 200) AS description, uri
									 FROM answers
									 WHERE title LIKE :query");
		$statement->bindValue('query', '%'.$query.'%');
		$statement->execute();
		$answers = $statement->fetchAll();

        return new JsonResponse(
            $answers
        );
    }

    /**
     * @ApiDoc(
     *  description="Part 2. Cars table. Returns cars grouped by years range.",
     *  tags={"JSON"}
     * )
     * @Route("/get-cars", name="api_get_cars")
     * @Method({"GET", "POST"})
     */
    public function getCarsAction()
    {
        $em = $this->getDoctrine()->getManager();

        $conn = $em->getConnection();

        $sql = "SELECT DISTINCT t2.fabrication_year,
                    SUM(t2.BMW) as BMW,
                    SUM(t2.Mercedes) as Mercedes,
                    SUM(t2.Audi) as Audi,
                    SUM(t2.Fiat) as Fiat
                FROM
                    (SELECT
                        CASE WHEN t1.fabrication_year BETWEEN 0 AND 1998 THEN '0-1998'
                        WHEN t1.fabrication_year BETWEEN 1999 AND 2002 THEN '1999-2002'
                        WHEN t1.fabrication_year BETWEEN 2003 AND 2006 THEN '2003-2006'
                        WHEN t1.fabrication_year BETWEEN 2007 AND 2009 THEN '2007-2009'
                        ELSE '2010-2016' END AS fabrication_year,
                        SUM(t1.BMW) as BMW,
                        SUM(t1.Mercedes) as Mercedes,
                        SUM(t1.Audi) as Audi,
                        SUM(t1.Fiat) as Fiat
                    FROM
                        (SELECT 
                        DISTINCT fabrication_year,
                        COUNT(CASE WHEN producer='Fiat' THEN producer END) AS Fiat,
                        COUNT(CASE WHEN producer='BMW' THEN producer END) AS BMW,
                        COUNT(CASE WHEN producer='Mercedes' THEN producer END) AS Mercedes,
                        COUNT(CASE WHEN producer='Audi' THEN producer END) AS Audi
                        FROM cars
                        GROUP BY fabrication_year) t1
                    GROUP BY t1.fabrication_year) t2
                GROUP BY fabrication_year;";

        $statement = $conn->prepare($sql);
        $statement->execute();
        $cars = $statement->fetchAll();        
        
        return new JsonResponse(
            $cars
        );
    }

    private function getSlug($value)
    {
        $slugify = new Slugify();
        return $slugify->slugify($value);
    }

    private function moveFile($file, $systemPath, $newFileName)
    {
        $file->move($systemPath, $newFileName);

        return true;
    }

    private function getSystemPath()
    {
        return $_SERVER['DOCUMENT_ROOT']."/files/";
    }

    private function getNewFilename($file)
    {
        return str_replace(".", "", microtime(true))."_".$this->getSlug($file->getClientOriginalName());
    }
}
