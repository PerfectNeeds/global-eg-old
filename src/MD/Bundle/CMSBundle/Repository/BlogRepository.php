<?php

namespace MD\Bundle\CMSBundle\Repository;

use Doctrine\ORM\EntityRepository;

class BlogRepository extends EntityRepository {

    public function getRandBlog($limit) {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT id FROM blog ORDER BY RAND() limit $limit";
        $statement = $connection->prepare($sql);
        $statement->execute();
        $queryResult = $statement->fetchAll();
        if (count($queryResult) == 0) {
            return NULL;
        }
        $result = array();
        foreach ($queryResult as $key => $r) {
            $result[$key] = $this->getEntityManager()->getRepository('CMSBundle:Blog')->find($r['id']);
        }
        return $result;
    }

}
