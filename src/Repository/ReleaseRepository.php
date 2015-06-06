<?php

namespace Lorry\Repository;

use Doctrine\ORM\EntityRepository;

class ReleaseRepository extends EntityRepository
{

    public function getLatestReleases()
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('r')
            ->from('Lorry\Model\Release', 'r')
            ->leftJoin('r.addon', 'a')
            ->leftJoin('a.game', 'g')
            ->where(':now >= r.published')
            ->orderBy('r.published', 'DESC')
            ->setParameter('now', new \DateTime());
         return $qb->getQuery()->getResult();
    }
}
