<?php

namespace MD\Bundle\CMSBundle\Controller\Administration;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MD\Bundle\CMSBundle\Entity\DynamicPage;
use MD\Bundle\CMSBundle\Form\DynamicPageType;
use Symfony\Component\HttpFoundation\Response;
use MD\Bundle\MediaBundle\Entity\Image as Image;
use Symfony\Component\HttpFoundation\Session\Session;
use MD\Bundle\CMSBundle\Entity\Post;
use MD\Bundle\CMSBundle\Entity\Seo;
use MD\Bundle\CMSBundle\Form\SeoType;

/**
 * DynamicPage controller.
 *
 * @Route("/page")
 */
class DynamicPageController extends Controller {

    /**
     * Lists all DynamicPage entities.
     *
     * @Route("/", name="dynamicpage")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('CMSBundle:DynamicPage')->findBy(array('cms' => TRUE));

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Lists all DynamicPage entities.
     *
     * @Route("/company", name="dynamicpage_company")
     * @Method("GET")
     * @Template("CMSBundle:Administration\DynamicPage:index.html.twig")
     */
    public function companyIndexAction() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('CMSBundle:DynamicPage')->findBy(array('cms' => FALSE));

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new DynamicPage entity.
     *
     * @Route("/", name="dynamicpage_create")
     * @Method("POST")
     * @Template("CMSBundle:Administration/DynamicPage:new.html.twig")
     */
    public function createAction(Request $request) {
        $entity = new DynamicPage();
        $form = $this->createForm(new DynamicPageType(), $entity);
        $form->bind($request);

        $seoEntity = new Seo();
        $seoForm = $this->createForm(new SeoType(), $seoEntity);
        $seoForm->bind($request);
        $seoEntity->setSlug('dynamic-page/' . $seoEntity->getSlug());

        $em = $this->getDoctrine()->getManager();
        $seoController = new SeoController($em);
        $seoController->validateSeo($seoEntity, $form);

        if ($form->isValid() AND $seoForm->isValid()) {
            $entity->setCms(TRUE);
            $em->persist($seoEntity);
            $em->flush();

            $description = $this->getRequest()->request->get('description');
            $brief = $this->getRequest()->request->get('brief');
            $content = array("description" => $description, 'brief' => $brief);
            $postEntity = new Post();
            $postEntity->setContent($content);
            $em->persist($postEntity);
            $em->flush();

            $entity->setSeo($seoEntity);
            $entity->setPost($postEntity);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('dynamicpage_edit', array('id' => $entity->getId())));
        }


        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'formSeo' => $seoForm->createView(),
        );
    }

    /**
     * Displays a form to create a new DynamicPage entity.
     *
     * @Route("/new", name="dynamicpage_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction() {
        $entity = new DynamicPage();
        $form = $this->createForm(new DynamicPageType(), $entity);

        $seoEntity = new Seo();
        $seoForm = $this->createForm(new SeoType(), $seoEntity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'formSeo' => $seoForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing DynamicPage entity.
     *
     * @Route("/{id}/edit", name="dynamicpage_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CMSBundle:DynamicPage')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DynamicPage entity.');
        }

        $editForm = $this->createForm(new DynamicPageType(), $entity);

        $seoForm = $this->createForm(new SeoType(), $entity->getSeo());

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'formSeo' => $seoForm->createView(),
        );
    }

    /**
     * Edits an existing DynamicPage entity.
     *
     * @Route("/{id}", name="dynamicpage_update")
     * @Method("PUT")
     * @Template("CMSBundle:Administration/DynamicPage:edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CMSBundle:DynamicPage')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DynamicPage entity.');
        }

        $editForm = $this->createForm(new DynamicPageType(), $entity);
        $editForm->bind($request);

        $seoForm = $this->createForm(new SeoType(), $entity->getSeo());
        $seoForm->bind($request);
        $entity->getSeo()->setSlug('dynamic-page/' . $entity->getSeo()->getSlug());

        $seoController = new SeoController($em);
        $seoController->validateSeo($entity->getSeo(), $editForm);

        if ($editForm->isValid() AND $seoForm->isValid()) {

            $description = $this->getRequest()->request->get('description');
            $brief = $this->getRequest()->request->get('brief');
            $content = array("description" => $description, 'brief' => $brief);
            $entity->getPost()->setContent($content);

            $em->persist($entity);
            $em->flush();

            if ($entity->getCms() == TRUE) {
                return $this->redirect($this->generateUrl('dynamicpage'));
            } else {
                return $this->redirect($this->generateUrl('dynamicpage_company'));
            }
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'formSeo' => $seoForm->createView(),
        );
    }

    /**
     * Deletes a DynamicPage entity.
     *
     * @Route("/delete", name="dynamicpage_delete")
     * @Method("POST")
     */
    public function deleteAction() {
        $cantDelete = array(1, 2, 3, 4, 5, 6, 7);

        $id = $this->getRequest()->request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:DynamicPage')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DynamicPage entity.');
        }

        if (in_array($entity->getId(), $cantDelete)) {
            $session = new Session();
            $session->getFlashBag()->add('error', 'Can\'t delete this page');
            return $this->redirect($this->generateUrl('dynamicpage'));
        }
        $em->remove($entity);
        $em->flush();
        return $this->redirect($this->generateUrl('dynamicpage'));
    }

    /**
     * Creates a form to delete a DynamicPage entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

    /**
     * Displays a form to create a new PropertyGallery entity.
     *
     * @Route("/gallery/{id}", name="dynamicpage_set_images")
     * @Method("GET")
     * @Template()
     */
    public function GetImagesAction($id, $imageTypes = NULL) {
        $form = $this->createForm(new \MD\Bundle\MediaBundle\Form\ImageType());
        $formView = $form->createView();
//        $formView->getChild('files')->set('full_name', 'files[]');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:Post')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find DynamicPage entity...');
        }
        $gallerImages = $entity->getImages();
        return array(
            'entity' => $entity,
            'form' => $formView,
            'id' => $id,
            'images' => $gallerImages,
            'mainImage' => Image::TYPE_MAIN
        );
    }

    /**
     * Set Images to Property.
     *
     * @Route("/gallery/{id}" , name="dynamicpage_create_images")
     * @Method("POST")
     */
    public function SetImageAction(Request $request, $id) {
        $form = $this->createForm(new \MD\Bundle\MediaBundle\Form\ImageType());
        $formView = $form->createView();
        $form->bind($request);

        $data = $form->getData();
        $files = $data["files"];

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:Post')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Landmark entity.');
        }
        $imageType = $request->get("type");
        foreach ($files as $file) {
            if ($file != NULL) {
                $image = new \MD\Bundle\MediaBundle\Entity\Image();
                $em->persist($image);
                $em->flush();
                $image->setFile($file);
                $mainImages = $entity->getImages(array(\MD\Bundle\MediaBundle\Entity\Image::TYPE_MAIN));
                if ($imageType == Image::TYPE_MAIN && count($mainImages) > 0) {
                    foreach ($mainImages As $mainImage) {
                        $entity->removeImage($mainImage);
                        $em->persist($entity);
                        $em->flush();
                        $image->storeFilenameForRemove("dynamicpages/" . $entity->getId());
                        $image->removeUpload();
//                        $image->storeFilenameForResizeRemove("suppliers/" . $entity->getId());
//                        $image->removeResizeUpload();
                        $em->persist($mainImage);
                        $em->flush();
                        $em->remove($mainImage);
                        $em->flush();
                        $image->setImageType(Image::TYPE_MAIN);
                    }
                } else if ($imageType == \MD\Bundle\MediaBundle\Entity\Image::TYPE_MAIN && count($mainImages) == 0) {
                    $image->setImageType(Image::TYPE_MAIN);
                } else {
                    $image->setImageType(Image::TYPE_GALLERY);
                }


                $image->preUpload();
                $image->upload("dynamicpages/" . $id);
                $image->setImageType(\MD\Bundle\MediaBundle\Entity\Image::TYPE_MCE);
                $entity->addImage($image);
                $imageUrl = $this->container->get('templating.helper.assets')->getUrl("uploads/dynamicpages/" . $id . "/" . $image->getId());
                $imageId = $image->getId();
            }
            $em->persist($entity);
            $em->flush();
            $files = '{"files":[{"url":"' . $imageUrl . '","thumbnailUrl":"http://lh6.ggpht.com/0GmazPJ8DqFO09TGp-OVK_LUKtQh0BQnTFXNdqN-5bCeVSULfEkCAifm6p9V_FXyYHgmQvkJoeONZmuxkTBqZANbc94xp-Av=s80","name":"test","id":"' . $imageId . '","type":"image/jpeg","size":620888,"deleteUrl":"http://localhost/packagat/web/uploads/packages/1/th71?delete=true","deleteType":"DELETE"}]}';
            $response = new Response();
            $response->setContent($files);
            $response->setStatusCode(200);
            return $response;
        }

        return array(
            'form' => $formView,
            'id' => $id,
        );
    }

    /**
     * Deletes a PropertyGallery entity.
     *
     * @Route("/deleteimage/{h_id}/{redirect_id}", name="dynamicpageimages_delete")
     * @Method("POST")
     */
    public function deleteImageAction($h_id, $redirect_id) {
        $image_id = $this->getRequest()->request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:Post')->find($h_id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find TourGallery entity.');
        }
        $image = $em->getRepository('MediaBundle:Image')->find($image_id);
        if (!$image) {
            throw $this->createNotFoundException('Unable to find TourGallery entity.');
        }
        $entity->removeImage($image);
        $em->persist($entity);
        $em->flush();
        $image->storeFilenameForRemove("dynamicpages/" . $h_id);
        $image->removeUpload();
//        $image->storeFilenameForResizeRemove("dynamicpages/" . $h_id);
//        $image->removeResizeUpload();
        $em->persist($image);
        $em->flush();
        $em->remove($image);
        $em->flush();

        if ($redirect_id == 1) {
            return $this->redirect($this->generateUrl('dynamicpage_edit', array('id' => $h_id)));
        } else if ($redirect_id == 2) {
            return $this->redirect($this->generateUrl('dynamicpage_edit', array('id' => $h_id)));
        }
    }

    /**
     * Displays a form to create a new PropertyGallery entity.
     *
     * @Route("/gallery/ajax/", name="dynamicpage_ajax")
     * @Method("POST")
     */
    public function SetimageMainAction() {
        $id = $this->getRequest()->request->get('id');
        $image_id = $this->getRequest()->request->get('image_id');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('MediaBundle:Image')->setMainImage('CMSBundle:Post', $id, $image_id);
    }

    /**
     * Displays a form to create a new PropertyGallery entity.
     *
     * @Route("/document/{id}", name="dynamicpage_set_documents")
     * @Method("GET")
     * @Template()
     */
    public function GetDocumentsAction($id, $documentTypes = NULL) {
        $form = $this->createForm(new \MD\Bundle\MediaBundle\Form\DocumentType());
        $formView = $form->createView();
//        $formView->getChild('files')->set('full_name', 'files[]');
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CMSBundle:Post')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity...');
        }
        $documents = $entity->getDocuments();
        return array(
            'entity' => $entity,
            'form' => $formView,
            'documents' => $documents
        );
    }

    /**
     * Deletes a PropertyGallery entity.
     *
     * @Route("/deletedocument/{h_id}/{redirect_id}", name="dynamicpagedocuments_delete")
     * @Method("POST")
     */
    public function deleteDocumentAction($h_id, $redirect_id) {
        $document_id = $this->getRequest()->request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:Post')->find($h_id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }
        $document = $em->getRepository('MediaBundle:Document')->find($document_id);
        if (!$document) {
            throw $this->createNotFoundException('Unable to find Document entity.');
        }
        $entity->removeDocument($document);
        $em->persist($entity);
        $em->flush();
        $document->storeFilenameForRemove("dynamicpages/document/" . $h_id);
        $document->removeUpload();
//        $document->storeFilenameForResizeRemove("dynamicpages/document/" . $h_id);
        $em->persist($document);
        $em->flush();
        $em->remove($document);
        $em->flush();

        if ($redirect_id == 1) {
            return $this->redirect($this->generateUrl('dynamicpage_set_documents', array('id' => $h_id)));
        } else if ($redirect_id == 2) {
            return $this->redirect($this->generateUrl('dynamicpage_edit', array('id' => $h_id)));
        }
    }

    /**
     * Set Documents to Property.
     *
     * @Route("/document/{id}" , name="dynamicpage_create_documents")
     * @Method("POST")
     */
    public function SetDocumentAction(Request $request, $id) {
        $form = $this->createForm(new \MD\Bundle\MediaBundle\Form\DocumentType());
        $formView = $form->createView();
        $form->bind($request);

        $data = $form->getData();
        $files = $data["files"];
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:Post')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Post entity.');
        }
        foreach ($files as $file) {
            if ($file != NULL) {
                $document = new \MD\Bundle\MediaBundle\Entity\Document($file);
                $em->persist($document);
                $em->flush();
//                $document->setFile($file);
                $document->preUpload();
                $document->upload("dynamicpages/document/" . $id);
                $entity->addDocument($document);
                $documentUrl = $this->container->get('templating.helper.assets')->getUrl("uploads/dynamicpages/document/" . $id . "/" . $document->getId());
                $documentId = $document->getId();
                $documentName = $document->getName();
            }
            $em->persist($entity);
            $em->flush();
            $files = '{"files":[{"url":"' . $documentUrl . '","thumbnailUrl":"' . $documentUrl . '","name":"' . $documentName . '","id":"' . $documentId . '","type":"document/jpeg","size":620888,"deleteUrl":"http://localhost/packagat/web/uploads/packages/1/th71?delete=true","deleteType":"DELETE"}]}';
            $response = new Response();
            $response->setContent($files);
            $response->setStatusCode(200);
            return $response;
        }

        return array(
            'form' => $formView,
            'id' => $id,
        );
    }

}
