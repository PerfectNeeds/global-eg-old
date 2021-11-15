<?php

namespace MD\Bundle\CMSBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DynamicPageRepository extends EntityRepository {

public function findOneBySlug($slug) {
        $connection = $this->getEntityManager();
        $entity = $connection->getRepository('CMSBundle:Seo')->findOneBySlug('dynamic-page/' . $slug);
        $entity = $entity->getDynamicPage();
        return $entity;
    }


    public function getRandClient($limit) {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT id FROM client ORDER BY RAND() limit $limit";
        $statement = $connection->prepare($sql);
        $statement->execute();
        $queryResult = $statement->fetchAll();
        if (count($queryResult) == 0) {
            return NULL;
        }
        $result = array();
        foreach ($queryResult as $key => $r) {
            $result[$key] = $this->getEntityManager()->getRepository('CMSBundle:Client')->find($r['id']);
        }
        return $result;
    }

    public function getRandPortfolio($limit) {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT id FROM portfolio ORDER BY RAND() limit $limit";
        $statement = $connection->prepare($sql);
        $statement->execute();
        $queryResult = $statement->fetchAll();
        if (count($queryResult) == 0) {
            return NULL;
        }
        $result = array();
        foreach ($queryResult as $key => $r) {
            $result[$key] = $this->getEntityManager()->getRepository('CMSBundle:Portfolio')->find($r['id']);
        }
        return $result;
    }

}
