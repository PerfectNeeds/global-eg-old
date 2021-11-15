<?php

namespace MD\Bundle\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use MD\Bundle\MediaBundle\Entity\Image;

/**
 * Image controller.
 *
 * @Route("/image")
 */
class ImageController extends Controller {

    private $type = array(
        1 => 'ad/', //Ad
        2 => 'banner/', //Banner
        3 => 'person/', //Banner
    );

    public function uploadSingleImage($em, $entity, $file, $type) {

        if ($file != null) {
            $uploadPath = $this->type[$type];

            $imageId = $entity->getId();

            $oldImage = $entity->getImage();
            if ($oldImage) {
                $oldImage->storeFilenameForRemove($uploadPath . $imageId);
                $oldImage->removeUpload();
                $em->persist($oldImage);
                $em->persist($entity);
            }

            $image = new Image();
            $em->persist($image);
            $em->flush();
            $image->setFile($file);
            $image->setImageType(Image::TYPE_GALLERY);
            $image->preUpload();
            $image->upload($uploadPath . $imageId);
            $entity->setImage($image);
            $em->persist($entity);
            $em->flush();
        }
    }

}
