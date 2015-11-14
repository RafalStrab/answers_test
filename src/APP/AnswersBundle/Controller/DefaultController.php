<?php

namespace APP\AnswersBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Admin Podcast controller.
 *
 * @Route("/")
 */
class DefaultController extends Controller
{
    /**
     * @ApiDoc(
     *  description="Application index page.",
     *  tags={"Main page"},
     * )
     * 
     * @Route("/", name="index")
     * @Template()
     */
    public function indexAction()
    {
        return array(
            
        );
    }

    /**
     * @ApiDoc(
     *  description="Attachment download.",
     *  tags={"file"},
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="attachment id"
     *      }
     *  },
     *  parameters={
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="attachment id"}
     *  }
     * )
     * 
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
