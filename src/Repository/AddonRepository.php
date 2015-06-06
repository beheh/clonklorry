<?php

namespace Lorry\Repository;

use Doctrine\ORM\EntityRepository;

class AddonRepository extends EntityRepository
{
    public function getAllByGame($game)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('a')
            ->from('Lorry\Model\Addon', 'a')
            ->leftJoin('a.latestRelease', 'r')
            ->leftJoin('a.game', 'g')
            ->where('a.game = :game')
            ->andWhere(':now >= r.published')
            ->orderBy('a.title', 'DESC')
            ->setParameter('now', new \DateTime())
            ->setParameter('game', $game);
        return $qb->getQuery()->getScalarResult();
    }
}
