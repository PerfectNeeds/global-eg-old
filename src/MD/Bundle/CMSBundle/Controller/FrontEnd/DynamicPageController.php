<?php

namespace MD\Bundle\CMSBundle\Controller\FrontEnd;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MD\Bundle\CMSBundle\Entity\DynamicPage;

/**
 * DynamicPage controller.
 *
 * @Route("/page")
 */
class DynamicPageController extends Controller {

    /**
     * Lists all DynamicPage entities.
     *
     * @Route("/{name}", name="fe_dynamicpage")
     * @Method("GET")
     * @Template()
     */
    public function aboutAction($name) {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('CMSBundle:DynamicPage')->findOneBySlug($name);
        $images = $page->getPost()->getImages(array(\MD\Bundle\MediaBundle\Entity\Image::TYPE_MCE));


        return array(
            'page' => $page,
            'images' => $images,
        );
    }

    /**
     * Lists all DynamicPage entities.
     *
     * @Route("/menu", name="fe_dynamicpage_menu")
     * @Method("GET")
     * @Template()
     */
    public function menuAction() {

        return array(
        );
    }

}
