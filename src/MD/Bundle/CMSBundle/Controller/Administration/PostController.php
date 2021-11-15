<?php

namespace MD\Bundle\CMSBundle\Controller\Administration;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use MD\Bundle\MediaBundle\Entity\Image;

/**
 * Post controller.
 *
 * @Route("/post")
 */
class PostController extends Controller {

    private $type = array(
        1 => array('Entity' => 'Artisan', 'Route' => 'artisan_edit', 'UploadPath' => 'artisan/'), //Artisan
        2 => array('Entity' => 'SuperCategory', 'Route' => 'supercategory_edit', 'UploadPath' => 'super-category/'), //SuperCategory
        3 => array('Entity' => 'Category', 'Route' => 'category_edit', 'UploadPath' => 'category/'), //Category
        4 => array('Entity' => 'SubCategory', 'Route' => 'subcategory_edit', 'UploadPath' => 'sub-category/'), //SubCategory
        5 => array('Entity' => 'Collection', 'Route' => 'collection_edit', 'UploadPath' => 'collection/'), //Collection
        6 => array('Entity' => 'SubCollection', 'Route' => 'subcollection_edit', 'UploadPath' => 'sub-collection/'), //SubCollection
        7 => array('Entity' => 'DynamicPage', 'Route' => 'dynamicpage_edit', 'UploadPath' => 'dynamic-page/'), //DynamicPage
        8 => array('Entity' => 'Heritage', 'Route' => 'heritage_edit', 'UploadPath' => 'heritage/'), //Heritage
        9 => array('Entity' => 'Product', 'Route' => 'product_edit', 'UploadPath' => 'product/'), //Product
    );

    /**
     * Displays a form to create a new PropertyGallery entity.
     *
     * @Route("/gallery/{id}/{pageType}/{parentId}", name="post_set_images")
     * @Method("GET")
     * @Template("CMSBundle:Administration/Post:getImages.html.twig")
     */
    public function getImagesAction($id, $pageType, $parentId, $imageTypes = NULL) {
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
            'parentId' => $parentId,
            'pageType' => $pageType,
            'parentRoute' => $this->generateUrl($this->type[$pageType]['Route'], array('id' => $parentId)),
            'pageTypeData' => $this->type[$pageType],
            'images' => $gallerImages,
            'mainImage' => Image::TYPE_MAIN
        );
    }

    /**
     * Set Images to Property.
     *
     * @Route("/gallery/{id}/{pageType}" , name="post_create_images")
     * @Method("POST")
     */
    public function SetImageAction(Request $request, $id, $pageType) {
        $form = $this->createForm(new \MD\Bundle\MediaBundle\Form\ImageType());
        $formView = $form->createView();
        $form->bind($request);

        $data = $form->getData();
        $files = $data["files"];

        $em = $this->getDoctrine()->getManager();
        $pageTypeEntity = $this->type[$pageType]['Entity'];

        $entity = $em->getRepository('CMSBundle:Post')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Landmark entity.');
        }

        $uploadPath = $this->type[$pageType]['UploadPath'];

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
                        $image->storeFilenameForRemove($uploadPath . $entity->getId());
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
                $image->upload($uploadPath . $id);
                $image->setImageType(\MD\Bundle\MediaBundle\Entity\Image::TYPE_MCE);
                $entity->addImage($image);
                $imageUrl = $this->container->get('templating.helper.assets')->getUrl("uploads/" . $uploadPath . $id . "/" . $image->getId());
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
     * @Route("/deleteimage/{parentId}/{postId}/{pageType}", name="postimages_delete")
     * @Method("POST")
     */
    public function deleteImageAction($parentId, $postId, $pageType) {
        $image_id = $this->getRequest()->request->get('id');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CMSBundle:Post')->find($postId);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to Post Team entity.');
        }
        $image = $em->getRepository('MediaBundle:Image')->find($image_id);
        if (!$image) {
            throw $this->createNotFoundException('Unable to find Team entity.');
        }
        $entity->removeImage($image);
        $em->persist($entity);
        $em->flush();

        $uploadPath = $this->type[$pageType]['UploadPath'];

        $image->storeFilenameForRemove($uploadPath . $entity->getId());
        $image->removeUpload();
//        $image->storeFilenameForResizeRemove($uploadPath . $h_id);
//        $image->removeResizeUpload();
//        $em->persist($image);
//        $em->flush();
        $em->remove($image);
        $em->flush();

        return $this->redirect($this->generateUrl('post_set_images', array('id' => $postId, 'pageType' => $pageType, 'parentId' => $parentId)));
    }

    /**
     * Displays a form to create a new PropertyGallery entity.
     *
     * @Route("/gallery/ajax/", name = "post_ajax")
     * @Method("POST")
     */
    public function SetimageMainAction() {
        $id = $this->getRequest()->request->get('id');
        $image_id = $this->getRequest()->request->get('image_id');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('MediaBundle:Image')->setMainImage('CMSBundle:Post', $id, $image_id);
    }

}
