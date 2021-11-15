<?php

namespace MD\Bundle\CMSBundle\Controller\Administration;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MD\Bundle\CMSBundle\Entity\Photo;
use MD\Bundle\CMSBundle\Form\PhotoType;

/**
 * Photo controller.
 *
 * @Route("/photo")
 */
class PhotoController extends Controller {

    /**
     * Lists all Photo entities.
     *
     * @Route("/{pId}", name="photo")
     * @Method("GET")
     * @Template()
     */
    public function indexAction($pId) {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CMSBundle:Photo')->findBy(array('photoAlbum' => $pId));
        $photoAlbum = $em->getRepository('CMSBundle:PhotoAlbum')->find($pId);

        return array(
            'entities' => $entities,
            'photoAlbum' => $photoAlbum,
        );
    }

    /**
     * Creates a new Photo entity.
     *
     * @Route("/{pId}", name="photo_create")
     * @Method("POST")
     * @Template("CMSBundle:Photo:new.html.twig")
     */
    public function createAction(Request $request, $pId) {
        $entity = new Photo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $photoAlbum = $em->getRepository('CMSBundle:PhotoAlbum')->find($pId);

        $uploadForm = $this->createForm(new \MD\Bundle\MediaBundle\Form\SingleImageType());
        $formView = $uploadForm->createView();
        $uploadForm->bind($request);
        $data_upload = $uploadForm->getData();
        $file = $data_upload["file"];

        $entity->setPhotoAlbum($photoAlbum);
        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();

            $photoId = $entity->getId();
            $image = new \MD\Bundle\MediaBundle\Entity\Image();
            $em->persist($image);
            $em->flush();
            $image->setFile($file);
            $image->setImageType(\MD\Bundle\MediaBundle\Entity\Image::TYPE_GALLERY);
            $image->preUpload();
            $image->upload("photo/" . $photoId);
            $entity->setImage($image);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('photo', array('pId' => $photoAlbum->getId())));
        }

        return array(
            'entity' => $entity,
            'photoAlbum' => $photoAlbum,
            'form' => $form->createView(),
            'upload_form' => $formView,
        );
    }

    /**
     * Creates a form to create a Photo entity.
     *
     * @param Photo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Photo $entity) {
        $form = $this->createForm(new PhotoType(), $entity, array(
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Displays a form to create a new Photo entity.
     *
     * @Route("/new/{pId}", name="photo_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($pId) {
        $entity = new Photo();
        $form = $this->createCreateForm($entity);

        $uploadForm = $this->createForm(new \MD\Bundle\MediaBundle\Form\SingleImageType());
        $formView = $uploadForm->createView();

        $em = $this->getDoctrine()->getManager();
        $photoAlbum = $em->getRepository('CMSBundle:PhotoAlbum')->find($pId);


        return array(
            'entity' => $entity,
            'photoAlbum' => $photoAlbum,
            'form' => $form->createView(),
            'upload_form' => $formView,
        );
    }

    /**
     * Displays a form to edit an existing Photo entity.
     *
     * @Route("/{id}/edit", name="photo_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CMSBundle:Photo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Photo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);


        $uploadForm = $this->createForm(new \MD\Bundle\MediaBundle\Form\SingleImageType());
        $formView = $uploadForm->createView();

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'upload_form' => $formView,
        );
    }

    /**
     * Creates a form to edit a Photo entity.
     *
     * @param Photo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Photo $entity) {
        $form = $this->createForm(new PhotoType(), $entity, array(
            'action' => $this->generateUrl('photo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));


        return $form;
    }

    /**
     * Edits an existing Photo entity.
     *
     * @Route("/{id}", name="photo_update")
     * @Method("PUT")
     * @Template("CMSBundle:Photo:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CMSBundle:Photo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Photo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        $uploadForm = $this->createForm(new \MD\Bundle\MediaBundle\Form\SingleImageType());
        $formView = $uploadForm->createView();
        $uploadForm->bind($request);
        $data_upload = $uploadForm->getData();
        $file = $data_upload["file"];

        if ($editForm->isValid()) {
            if ($file != null) {
                $oldImage = $entity->getImage();
                if ($oldImage) {
                    $oldImage->storeFilenameForRemove("photo/" . $id);
                    $oldImage->removeUpload();
                    $em->persist($oldImage);
                    $em->persist($entity);
                }
                $image = new \MD\Bundle\MediaBundle\Entity\Image();
                $em->persist($image);
                $em->flush();
                $image->setFile($file);
                $image->setImageType(\MD\Bundle\MediaBundle\Entity\Image::TYPE_GALLERY);
                $image->preUpload();
                $image->upload("photo/" . $id);
                $entity->setImage($image);
            }

            $em->flush();

            return $this->redirect($this->generateUrl('photo_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Photo entity.
     *
     * @Route("/delete/{pId}", name="photo_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $pId) {
        $id = $this->getRequest()->request->get('id');
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:Photo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Photo entity.');
        }
        $oldImage = $entity->getImage();
        $oldImage->storeFilenameForRemove("photo/" . $id);
        $oldImage->removeUpload();
        $image = new \MD\Bundle\MediaBundle\Entity\Image;
        $image->storeDirectoryForRemove("photo/" . $id);
        $image->removeDirectoryUpload();
        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('photo', array('pId' => $entity->getPhotoAlbum()->getId())));
    }

    /**
     * Creates a form to delete a Photo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    /**
     * Deletes a PropertyGallery entity.
     *
     * @Route("/deleteimage/{h_id}/{redirect_id}", name="photoimages_delete")
     * @Method("POST")
     */
    public function deleteImageAction($h_id, $redirect_id) {
        $image_id = $this->getRequest()->request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:Photo')->find($h_id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Photo entity.');
        }
        $image = $em->getRepository('MediaBundle:Image')->find($image_id);
        if (!$image) {
            throw $this->createNotFoundException('Unable to find TourGallery entity.');
        }
        $entity->removeImage($image);
        $em->persist($entity);
        $em->flush();
        $image->storeFilenameForRemove("photo/" . $h_id);
        $image->removeUpload();
//        $image->storeFilenameForResizeRemove("photos/" . $h_id);
//        $image->removeResizeUpload();
        $em->persist($image);
        $em->flush();
        $em->remove($image);
        $em->flush();

        if ($redirect_id == 1) {
            return $this->redirect($this->generateUrl('photo_edit', array('id' => $h_id)));
        } else if ($redirect_id == 2) {
            return $this->redirect($this->generateUrl('photo_edit', array('id' => $h_id)));
        }
    }

}
