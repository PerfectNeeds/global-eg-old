<?php

namespace MD\Bundle\CMSBundle\Controller\Administration;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MD\Bundle\CMSBundle\Entity\Blog;
use MD\Bundle\CMSBundle\Form\BlogType;
use MD\Bundle\MediaBundle\Entity\Image;
use Symfony\Component\HttpFoundation\Response;
use MD\Bundle\MediaBundle\Form As MediaForm;
use Symfony\Component\HttpFoundation\Session\Session;
use MD\Bundle\CMSBundle\Entity\Seo;
use MD\Bundle\CMSBundle\Form\SeoType;
use MD\Bundle\CMSBundle\Entity\Post;

/**
 * Blog controller.
 *
 * @Route("/blog")
 */
class BlogController extends Controller {

    /**
     * Lists all Blog entities.
     *
     * @Route("/", name="blog")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CMSBundle:Blog')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new Blog entity.
     *
     * @Route("/", name="blog_create")
     * @Method("POST")
     * @Template("CMSBundle:Administration/Blog:new.html.twig")
     */
    public function createAction(Request $request) {
        $entity = new Blog();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $seoEntity = new Seo();
        $seoForm = $this->createForm(new SeoType(), $seoEntity);
        $seoForm->bind($request);
        $seoEntity->setSlug('blog/' . $seoEntity->getSlug());

        $seoController = new SeoController($em);
        $seoController->validateSeo($seoEntity, $form);


        if ($form->isValid() AND $seoForm->isValid()) {
            $em->persist($seoEntity);
            $em->flush();

            $brief = $this->getRequest()->request->get('brief');
            $contentArray = array("brief" => $brief);
            $postEntity = new Post();
            $postEntity->setContent($contentArray);
            $em->persist($postEntity);
            $em->flush();

            $entity->setSeo($seoEntity);
            $entity->setPost($postEntity);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('blog'));
        }

        return array(
            'entity' => $entity,
            'blogCategory' => $blogCثategory,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Blog entity.
     *
     * @param Blog $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Blog $entity) {
        $form = $this->createForm(new BlogType(), $entity, array(
            'method' => 'POST',
        ));


        return $form;
    }

    /**
     * Displays a form to create a new Blog entity.
     *
     * @Route("/new", name="blog_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction() {
        $entity = new Blog();
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
     * Displays a form to edit an existing Blog entity.
     *
     * @Route("/{id}/edit", name="blog_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CMSBundle:Blog')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Blog entity.');
        }

        $editForm = $this->createEditForm($entity);
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
    private function createEditForm(Blog $entity) {
        $form = $this->createForm(new BlogType(), $entity, array(
            'action' => $this->generateUrl('blog_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));


        return $form;
    }

    /**
     * Edits an existing Blog entity.
     *
     * @Route("/{id}", name="blog_update")
     * @Method("PUT")
     * @Template("CMSBundle:Blog:Administration/edit.html.twig")
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CMSBundle:Blog')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Blog entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        $seoForm = $this->createForm(new SeoType(), $entity->getSeo());
        $seoForm->bind($request);
        $entity->getSeo()->setSlug('blog/' . $entity->getSeo()->getSlug());

        $seoController = new SeoController($em);
        $seoController->validateSeo($entity->getSeo(), $editForm);

        if ($editForm->isValid() AND $seoForm->isValid()) {

            $brief = $this->getRequest()->request->get('brief');
            $content = array('brief' => $brief);
            $entity->getPost()->setContent($content);

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('blog_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a Blog entity.
     *
     * @Route("/delete", name="blog_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request) {
        $id = $this->getRequest()->request->get('id');
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:Blog')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Blog entity.');
        }

        $entity->setDeleted(TRUE);
        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('blog', array('cId' => $entity->getBlogCategory()->getId())));
    }

    /**
     * Creates a form to delete a Blog entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

    /**
     * Displays a form to create a new PropertyGallery entity.
     *
     * @Route("/gallery/{id}", name="blog_set_images")
     * @Method("GET")
     * @Template()
     */
    public function GetImagesAction($id, $imageTypes = NULL) {
        $form = $this->createForm(new \MD\Bundle\MediaBundle\Form\ImageType());
        $formView = $form->createView();
//        $formView->getChild('files')->set('full_name', 'files[]');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:Blog')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Blog entity...');
        }
        $productFamily = $em->getRepository('CMSBundle:Blog')->find($id);
        $gallerImages = $productFamily->getImages();
        return array(
            'entity' => $entity,
            'form' => $formView,
            'id' => $id,
            'images' => $gallerImages
        );
    }

    /**
     * Deletes a PropertyGallery entity.
     *
     * @Route("/deleteimage/{h_id}/{redirect_id}", name="blogimages_delete")
     * @Method("POST")
     */
    public function deleteImageAction($h_id, $redirect_id) {
        $image_id = $this->getRequest()->request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:Blog')->find($h_id);
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
        $image->storeFilenameForRemove("blog/" . $h_id);
        $image->removeUpload();
        $image->storeFilenameForResizeRemove("blog/" . $h_id);
//        $image->removeResizeUpload();
        $em->persist($image);
        $em->flush();
        $em->remove($image);
        $em->flush();

        if ($redirect_id == 1) {
            return $this->redirect($this->generateUrl('blog_set_images', array('id' => $h_id)));
        } else if ($redirect_id == 2) {
            return $this->redirect($this->generateUrl('blog_edit', array('id' => $h_id)));
        }
    }

    /**
     * Displays a form to create a new PropertyGallery entity.
     *
     * @Route("/gallery/ajax/", name="blog_ajax")
     * @Method("POST")
     */
    public function SetimageMainAction() {
        $id = $this->getRequest()->request->get('id');
        $image_id = $this->getRequest()->request->get('image_id');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('MediaBundle:Image')->setMainImage('CMSBundle:Blog', $id, $image_id);
    }

    // upload single Image 
    /**
     * Set Images to Property.
     *
     * @Route("/gallery/{id}" , name="blog_create_images")
     * @Method("POST")
     */
    public function SetImageAction(Request $request, $id) {
        $form = $this->createForm(new \MD\Bundle\MediaBundle\Form\ImageType());
        $formView = $form->createView();
        $form->bind($request);

        $data = $form->getData();
        $files = $data["files"];

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:Blog')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Landmark entity.');
        }

        foreach ($files as $file) {
            if ($file != NULL) {
                $image = new Image();
                $em->persist($image);
                $em->flush();
                $image->setFile($file);
                $image->preUpload();
                $image->upload("blog/" . $id);
                $image->setImageType(Image::TYPE_GALLERY);
                /*      list($width, $height) = getimagesize($image->getUploadDirForResize("propertiers/" . $id) . "/" . $image->getId());
                  $oPath = $image->getUploadDirForResize("propertiers/" . $id) . "/" . $image->getId();
                  if ($width != 870 || $height != 420) {
                  $resize_1 = $image->getUploadDirForResize("compound/" . $id) . "/" . $image->getId();
                  \MD\Bundle\MediaBundle\Utils\SimpleImage::saveNewResizedImage($oPath, $resize_1, 870, 420);
                  }
                  $resize_2 = $image->getUploadDirForResize("compound/" . $id) . "/" . "th" . $image->getId();
                  \MD\Bundle\MediaBundle\Utils\SimpleImage::saveNewResizedImage($oPath, $resize_2, 270, 203); */
                $entity->addImage($image);
                $imageUrl = $this->container->get('templating.helper.assets')->getUrl("uploads/blog/" . $id . "/" . $image->getId());
                $imageId = $image->getId();
            }
            $em->persist($entity);
            $em->flush();
            $files = '{"files":[{"url":"' . $imageUrl . '","thumbnailUrl":"' . $imageUrl . '","name":"test","id":"' . $imageId . '","type":"image/jpeg","size":620888,"deleteUrl":"http://localhost/packagat/web/uploads/packages/1/th71?delete=true","deleteType":"DELETE"}]}';
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
