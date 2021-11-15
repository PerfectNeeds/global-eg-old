<?php

namespace MD\Bundle\CMSBundle\Controller\Administration;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MD\Bundle\CMSBundle\Entity\PhotoAlbum;
use MD\Bundle\CMSBundle\Form\PhotoAlbumType;
use MD\Bundle\CMSBundle\Entity\PhotoAlbumCategory;
use MD\Bundle\CMSBundle\Form\SeoType;
use MD\Bundle\CMSBundle\Entity\Seo;

/**
 * PhotoAlbum controller.
 *
 * @Route("/photoalbum")
 */
class PhotoAlbumController extends Controller {

    /**
     * Lists all PhotoAlbum entities.
     *
     * @Route("/", name="photoalbum")
     * @Method("GET")
     * @Template("CMSBundle:Administration\PhotoAlbum:index.html.twig")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CMSBundle:PhotoAlbum')->findAll();

        return array(
            'entities' => $entities,
            'pageTitle' => 'Product Photo Albums',
            'pageNewRoute' => 'photoalbum_product_new',
            'formEditRoute' => 'photoalbum_product_edit',
        );
    }

    /**
     * Creates a new PhotoAlbum entity.
     *
     * @Route("/", name="photoalbum_create")
     * @Method("POST")
     * @Template("CMSBundle:Administration\PhotoAlbum:new.html.twig")
     */
    public function createAction(Request $request) {
        $entity = new PhotoAlbum();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $seoEntity = new Seo();
        $seoForm = $this->createForm(new SeoType(), $seoEntity);
        $seoForm->bind($request);
        $seoEntity->setSlug('photo-album/' . $seoEntity->getSlug());

        if ($form->isValid()) {

            $em->persist($seoEntity);
            $em->flush();

            $em->persist($entity);
            $em->flush();

            $entity->setSeo($seoEntity);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('photoalbum'));
        }

        $categoies = $em->getRepository('CMSBundle:PhotoAlbumCategory')->findBy(array('type' => PhotoAlbumCategory::T_PROJECT));
        return array(
            'entity' => $entity,
            'categoies' => $categoies,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a PhotoAlbum entity.
     *
     * @param PhotoAlbum $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(PhotoAlbum $entity) {
        $form = $this->createForm(new PhotoAlbumType(), $entity, array(
            'action' => $this->generateUrl('photoalbum_create'),
            'method' => 'POST',
        ));


        return $form;
    }

    /**
     * Displays a form to create a new PhotoAlbum entity.
     *
     * @Route("/new", name="photoalbum_new")
     * @Method("GET")
     * @Template("CMSBundle:Administration\PhotoAlbum:new.html.twig")
     */
    public function newAction() {
        $entity = new PhotoAlbum();
        $form = $this->createCreateForm($entity);

        $seoEntity = new Seo();
        $seoForm = $this->createForm(new SeoType(), $seoEntity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'formSeo' => $seoForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing PhotoAlbum entity.
     *
     * @Route("/{id}/edit", name="photoalbum_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:PhotoAlbum')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PhotoAlbum entity.');
        }

        $editForm = $this->createForm(new PhotoAlbumType(), $entity);
        $seoForm = $this->createForm(new SeoType(), $entity->getSeo());


        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'formSeo' => $seoForm->createView(),
        );
    }

    /**
     * Creates a form to edit a Blog entity.
     *
     * @param Blog $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(PhotoAlbum $entity) {
        $form = $this->createForm(new PhotoAlbumType(), $entity, array(
            'action' => $this->generateUrl('photoalbum_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));


        return $form;
    }

    /**
     * Edits an existing PhotoAlbum entity.
     *
     * @Route("/{id}", name="photoalbum_update")
     * @Method("PUT")
     * @Template("CMSBundle:Administration\PhotoAlbum:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CMSBundle:PhotoAlbum')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PhotoAlbum entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        $seoForm = $this->createForm(new SeoType(), $entity->getSeo());
        $seoForm->bind($request);
        $entity->getSeo()->setSlug('photo-album/' . $entity->getSeo()->getSlug());

        $seoController = new SeoController($em);
        $seoController->validateSeo($entity->getSeo(), $editForm);
        if ($editForm->isValid()) {
            $em->flush();
        }
        return $this->redirect($this->generateUrl('photoalbum_edit', array('id' => $id)));

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'formSeo' => $seoForm->createView(),
        );
    }

    /**
     * Edits an existing PhotoAlbum entity.
     *
     * @Route("/{id}/product", name="photoalbum_product_update")
     * @Method("PUT")
     * @Template("CMSBundle:Administration\PhotoAlbum:edit.html.twig")
     */
    public function updateProductAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CMSBundle:PhotoAlbum')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PhotoAlbum entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $photoAlbumCategory = $this->getRequest()->request->get('photoAlbumCategory');
            $photoAlbumCategory = $em->getRepository('CMSBundle:PhotoAlbumCategory')->find($photoAlbumCategory);
            $entity->setPhotoAlbumCategory($photoAlbumCategory);
            $em->flush();

            return $this->redirect($this->generateUrl('photoalbum_product_edit', array('id' => $id)));
        }

        $categoies = $em->getRepository('CMSBundle:PhotoAlbumCategory')->findAll();


        return array(
            'entity' => $entity,
            'categoies' => $categoies,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a PhotoAlbum entity.
     *
     * @Route("/delete", name="photoalbum_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request) {
        $id = $this->getRequest()->request->get('id');

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:PhotoAlbum')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PhotoAlbum entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('photoalbum'));
    }

    /**
     * Creates a form to delete a PhotoAlbum entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('photoalbum_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

}
