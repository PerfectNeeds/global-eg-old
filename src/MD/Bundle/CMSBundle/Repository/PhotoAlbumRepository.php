<?php

namespace MD\Bundle\CMSBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PhotoAlbumRepository extends EntityRepository {

    public function findOneBySlug($slug) {
        $connection = $this->getEntityManager();
        $entity = $connection->getRepository('CMSBundle:Seo')->findOneBySlug('photo-album/' . $slug);
        $entity = $entity->getPhotoAlbum();
        return $entity;
    }

    public function getRandAlbum($limit) {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT id FROM photo_album WHERE placement in (3,5,7) ORDER BY RAND() limit $limit";
        $statement = $connection->prepare($sql);
        $statement->execute();
        $queryResult = $statement->fetchAll();
        if (count($queryResult) == 0) {
            return NULL;
        }
        $result = array();
        foreach ($queryResult as $key => $r) {
            $result[$key] = $this->getEntityManager()->getRepository('CMSBundle:PhotoAlbum')->find($r['id']);
        }
        return $result;
    }

}
