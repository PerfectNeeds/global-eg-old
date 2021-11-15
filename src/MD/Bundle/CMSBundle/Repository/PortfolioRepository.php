<?php

namespace MD\Bundle\CMSBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PortfolioRepository extends EntityRepository {

    public function queryNextPortfolio($currentId) {
        $connection = $this->getEntityManager()->getConnection();

        $sql = "SELECT * FROM portfolio WHERE id < :currentId Limit 1 ";

        $statement = $connection->prepare($sql);
        $statement->bindValue("currentId", $currentId);
        $statement->execute();

        $queryResult = $statement->fetch();
        if (!$queryResult) {
            return FALSE;
        }
        $result = $this->getEntityManager()->getRepository('CMSBundle:Portfolio')->find($queryResult['id']);

        if ($result == null) {
            return;
        } else {
            return $result;
        }
    }

    public function queryPrevPortfolio($currentId) {
        $connection = $this->getEntityManager()->getConnection();

        $sql = "SELECT * FROM portfolio WHERE id > :currentId Limit 1 ";

        $statement = $connection->prepare($sql);
        $statement->bindValue("currentId", $currentId);
        $statement->execute();

        $queryResult = $statement->fetch();
        if (!$queryResult) {
            return FALSE;
        }
        $result = $this->getEntityManager()->getRepository('CMSBundle:Portfolio')->find($queryResult['id']);

        if ($result == null) {
            return;
        } else {
            return $result;
        }
    }

}
