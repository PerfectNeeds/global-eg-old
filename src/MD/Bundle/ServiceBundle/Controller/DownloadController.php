<?php

namespace MD\Bundle\ServiceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Download controller.
 *
 * @Route("/download")
 */
class DownloadController extends Controller {

    private $file;
    private $documentId; // document id from document Entity
    private $elementId; // this var is elemnet id like (blog, dynamic page)
    private $type; // this var is download file type like (CONST BANNER, CONST DYNAMIC_PAGES, etc...)
    private $path = array(
        '11' => 'uploads/publications/document/%s/%s', //curriculum-vitae
        '12' => 'uploads/dynamicpages/document/%s/%s', //dynamicpages
        '13' => 'uploads/pressrelease-documents/%s/%s', //dynamicpages
        '14' => 'uploads/pressrelease-letter-documents/%s/%s', //curriculum-vitae
    );

    public function __construct() {
        $request = Request::createFromGlobals();
        $getParameter = $request->query->get('d'); // ex: {{ path('download', {'d': '{"document":'~document.id~',"element":'~entity.id~',"type":12}'}) }}
        $parameter = json_decode($getParameter, true);
        $this->documentId = $parameter['document'];
        $this->elementId = $parameter['element'];
        $this->type = $parameter['type'];
        $this->file = sprintf($this->path[$this->type], $this->elementId, $this->documentId);
    }

    private function getMimeType() {
        return mime_content_type($this->file);
    }

    public function getDownloadName() {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('MediaBundle:Document')->find($this->documentId);
        return $entity->getName() . '.' . $entity->getExtension();
    }

    /**
     * test page.
     *
     * @Route("/", name="download")
     * @Method("GET")
     * @Template()
     */
    public function DownloadAction() {

        // Generate response
        $response = new Response();

        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', $this->getMimeType());
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $this->getDownloadName() . '"');
        $response->headers->set('Content-length', filesize($this->file));
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        // Send headers before outputting anything
        $response->sendHeaders();

        $response->setContent(readfile($this->file));
        return $response;
    }

}
