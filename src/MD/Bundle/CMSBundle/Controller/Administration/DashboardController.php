<?php

namespace MD\Bundle\CMSBundle\Controller\Administration;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * Faq controller.
 *
 * @Route("/")
 */
class DashboardController extends Controller {

    /**
     * Lists all Faq entities.
     *
     * @Route("/", name="cms_dashboard")
     * @Method("GET")
     * @Template()
     */
    public function DashboardAction() {
        return $this->redirect($this->generateUrl('dynamicpage'));
    }

}
