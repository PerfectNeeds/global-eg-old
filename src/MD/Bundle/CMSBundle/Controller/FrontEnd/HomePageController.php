<?php

namespace MD\Bundle\CMSBundle\Controller\FrontEnd;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * HomePage controller.
 *
 * @Route("")
 */
class HomePageController extends Controller {

    /**
     * Lists all Home entities.
     *
     * @Route("/", name="fe_home")
     * @Method("GET")
     * @Template()
     */
    public function homeAction() {
        $em = $this->getDoctrine()->getManager();
        $companies = $em->getRepository('CMSBundle:DynamicPage')->findBy(array('cms' => FALSE));
        $portfolios = $em->getRepository('CMSBundle:Portfolio')->findBy(array('homePage' => TRUE), array(), 6);
        $clients = $em->getRepository('CMSBundle:Client')->findBy(array('homePage' => TRUE), array(), 8);
        return array(
            'companies' => $companies,
            'clients' => $clients,
            'portfolios' => $portfolios,
        );
    }

    /**
     * Lists all Home entities.
     *
     * @Route("/team", name="fe_team")
     * @Method("GET")
     * @Template("CMSBundle:FrontEnd\Team:team.html.twig")
     */
    public function teamAction() {
        $em = $this->getDoctrine()->getManager();
        $teams = $em->getRepository('CMSBundle:Team')->findAll();
//        $secondRows = $em->getRepository('CMSBundle:Team')->findByPlacement(2);
//        $engineerings = $em->getRepository('CMSBundle:Team')->findByPlacement(3);
//        $meds = $em->getRepository('CMSBundle:Team')->findByPlacement(4);
//        $technologys = $em->getRepository('CMSBundle:Team')->findByPlacement(5);
//        $sales = $em->getRepository('CMSBundle:Team')->findByPlacement(6);
//        $reals = $em->getRepository('CMSBundle:Team')->findByPlacement(7);


        return array(
            'teams' => $teams,
//            'firstRows' => $firstRows,
//            'secondRows' => $secondRows,
//            'engineerings' => $engineerings,
//            'meds' => $meds,
//            'technologys' => $technologys,
//            'sales' => $sales,
//            'reals' => $reals,
        );
    }

    /**
     * Lists all Home entities.
     *
     * @Route("/about", name="fe_about")
     * @Method("GET")
     * @Template("CMSBundle:FrontEnd\DynamicPage:about.html.twig")
     */
    public function aboutAction() {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('CMSBundle:DynamicPage')->find(1);
        return array(
            'page' => $page,
        );
    }

    /**
     * Lists all Home entities.
     * 
     * @Route("/our-strategy", name="fe_our_strategy")
     * @Method("GET")
     * @Template("CMSBundle:FrontEnd\DynamicPage:about.html.twig")
     */
    public function ourStrategyAction() {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('CMSBundle:DynamicPage')->find(2);
        return array(
            'page' => $page,
        );
    }

    /**
     * Lists all Home entities.
     *
     * @Route("/global-engineering-supply", name="fe_global_engineering_supply")
     * @Method("GET")
     * @Template("CMSBundle:FrontEnd\DynamicPage:about.html.twig")
     */
    public function GlobalEngineeringSupplyAction() {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('CMSBundle:DynamicPage')->find(3);
        return array(
            'page' => $page,
        );
    }

    /**
     * Lists all Home entities.
     *
     * @Route("/med-trade", name="fe_med_trade")
     * @Method("GET")
     * @Template("CMSBundle:FrontEnd\DynamicPage:about.html.twig")
     */
    public function MedTradeAction() {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('CMSBundle:DynamicPage')->find(4);
        return array(
            'page' => $page,
        );
    }

    /**
     * Lists all Home entities.
     *
     * @Route("/technology", name="fe_technology")
     * @Method("GET")
     * @Template("CMSBundle:FrontEnd\DynamicPage:about.html.twig")
     */
    public function TechnologyAction() {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('CMSBundle:DynamicPage')->find(5);
        return array(
            'page' => $page,
        );
    }

    /**
     * Lists all Home entities.
     *
     * @Route("/4sale-egypt", name="fe_4sale_egypt")
     * @Method("GET")
     * @Template("CMSBundle:FrontEnd\DynamicPage:about.html.twig")
     */
    public function saleEgyptAction() {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('CMSBundle:DynamicPage')->find(6);
        return array(
            'page' => $page,
        );
    }

    /**
     * Lists all Home entities.
     *
     * @Route("/global-for-real-estate-development", name="fe_global_for_real_estate_development")
     * @Method("GET")
     * @Template("CMSBundle:FrontEnd\DynamicPage:about.html.twig")
     */
    public function globalforRealEstateDevelopmentAction() {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('CMSBundle:DynamicPage')->find(7);
        return array(
            'page' => $page,
        );
    }

    /**
     * @Template("CMSBundle:FrontEnd\HomePage:sidebarWidget.html.twig")
     */
    public function sidebarWidgetAction() {

        $em = $this->getDoctrine()->getManager();
        $bestSellings = $em->getRepository('CMSBundle:Product')->findRandomByLimit(3);
        $bloggers = $em->getRepository('CMSBundle:Blogger')->findBy(array(), array('id' => 'DESC'), 5);
        return array(
            'bestSellings' => $bestSellings,
            'bloggers' => $bloggers,
        );
    }

    /**
     * @Route("/clients/{name}/{pageId}", name="fe_client_with_name")
     * @Method("GET")
     * @Template("CMSBundle:FrontEnd\HomePage:clientWithName.html.twig")
     */
    public function clientWithNameAction($name, $pageId) {

        $em = $this->getDoctrine()->getManager();
        $clients = $em->getRepository('CMSBundle:Client')->findByPlacement($pageId);
        return array(
            'clients' => $clients,
            'name' => $name,
        );
    }

    /**
     * @Route("/clients/{name}/{pageId}", name="fe_client")
     * @Method("GET")
     * @Template("CMSBundle:FrontEnd\HomePage:client.html.twig")
     */
    public function clientAction($name, $pageId) {

        $em = $this->getDoctrine()->getManager();
        $clients = $em->getRepository('CMSBundle:Client')->findByPlacement($pageId);
        return array(
            'clients' => $clients,
            'name' => $name,
        );
    }

    /**
     * @Route("/portfolio/{pageId}", name="fe_portfolio")
     * @Method("GET")
     * @Template("CMSBundle:FrontEnd\HomePage:portfolio.html.twig")
     */
    public function portfolioAction($pageId) {

        $em = $this->getDoctrine()->getManager();
        $portfolios = $em->getRepository('CMSBundle:Portfolio')->findByPlacement($pageId);
        return array(
            'portfolios' => $portfolios,
        );
    }

    /**
     * @Route("/album-widget", name="fe_album_widget")
     * @Method("GET")
     * @Template("CMSBundle:FrontEnd\HomePage:album.html.twig")
     */
    public function albumBaseWidgetAction() {

        $em = $this->getDoctrine()->getManager();
        $photoAlbums = $em->getRepository('CMSBundle:PhotoAlbum')->getRandAlbum(6);
        return array(
            'photoAlbums' => $photoAlbums,
        );
    }

    /**
     * @Route("/blog", name="fe_blog_widget")
     * @Method("GET")
     * @Template("CMSBundle:FrontEnd\HomePage:blog.html.twig")
     */
    public function blogAction() {

        $em = $this->getDoctrine()->getManager();
        $blogs = $em->getRepository('CMSBundle:Blog')->getRandBlog(2);
        return array(
            'blogs' => $blogs,
        );
    }

}
