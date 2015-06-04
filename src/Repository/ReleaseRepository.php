<?php

namespace Lorry\Repository;

use Doctrine\ORM\EntityRepository;

class ReleaseRepository extends EntityRepository
{

    public function getLatestUniquePublishedReleases()
    {
        return $this->_em->createQuery('SELECT r FROM Lorry\Model\Release r WHERE r.published > :now ORDER BY r.published DESC')
                ->setParameter('now', new \DateTime())
                ->getResult();
    }
}
