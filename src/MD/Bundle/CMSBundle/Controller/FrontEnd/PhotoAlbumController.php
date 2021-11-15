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
 * @Route("media-center")
 */
class PhotoAlbumController extends Controller {

    /**
     * Lists all Home entities.
     *
     * @Route("/", name="fe_photo_album")
     * @Method("GET")
     * @Template()
     */
    public function albumAction() {
        $em = $this->getDoctrine()->getManager();
$photoAlbums = $em->getRepository('CMSBundle:PhotoAlbum')->findBy(array('placement'=>array('3','5','7')), array('id' => 'DESC'));
//        $photoAlbums = $em->getRepository('CMSBundle:PhotoAlbum')->findBy(array(), array('id' => 'DESC'));

        return array(
            'photoAlbums' => $photoAlbums,
        );
    }

    /**
     * Lists all Home entities.
     *
     * @Route("/{htmlSlug}", name="fe_show_photo_album")
     * @Method("GET")
     * @Template()
     */
    public function albumDetailsAction($htmlSlug) {
        $em = $this->getDoctrine()->getManager();
        $photoAlbum = $em->getRepository('CMSBundle:PhotoAlbum')->findOneBySlug($htmlSlug);
        $photos = $em->getRepository('CMSBundle:Photo')->findBy(array('photoAlbum' => $photoAlbum->getId()));

        return array(
            'photoAlbum' => $photoAlbum,
            'photos' => $photos,
        );
    }

    /**
     * Lists all Home entities.
     *
     * @Route("/videos", name="fe_video")
     * @Method("GET")
     * @Template()
     */
    public function videoAction() {
        $em = $this->getDoctrine()->getManager();
        $videos = $em->getRepository('MediaBundle:Video')->findBy(array(), array('id' => 'DESC'));

        return array(
            'videos' => $videos,
        );
    }

}
