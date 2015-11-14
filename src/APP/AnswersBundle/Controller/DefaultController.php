<?php

namespace APP\AnswersBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Admin Podcast controller.
 *
 * @Route("/")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     * @Template()
     */
    public function indexAction()
    {
        return array(
            
        );
    }

    /**
     * @Route("/attachment/{id}", name="index_attachment_download")
     * @Method("GET")
     */
    public function attachmentDownloadAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $attachment = $em->getRepository('APPAnswersBundle:Attachment')->find($id);

        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', $attachment->getMimeType());
        $response->headers->set('Content-Disposition', 'inline; filename="'.$attachment->getOriginalFilename().'";');
        $response->headers->set('Content-length', $attachment->getSize());

        $response->sendHeaders();
        $file = $attachment->getSystemPath().$attachment->getSystemFilename();
        $response->setContent(readfile($file));

        return $response;
    }
}
