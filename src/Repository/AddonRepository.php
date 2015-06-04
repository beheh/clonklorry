<?php

namespace Lorry\Repository;

use Doctrine\ORM\EntityRepository;

class AddonRepository extends EntityRepository
{

    public function getAllByGame($game)
    {
        //SELECT a.short, r.version FROM Lorry\Model\Addon a LEFT JOIN a.latestRelease r')

        $qb = $this->_em->createQueryBuilder()
            ->select('a.short, a.title, r.version')
            ->from('Lorry\Model\Addon', 'a')
            ->leftJoin('a.latestRelease', 'r')
            ->where('a.game = :game')
            //->andWhere('r.published > :now')
            //->orderBy('a.title', 'DESC')
            //->setParameter('now', new \DateTime())
            ->setParameter('game', $game);
        return $qb->getQuery()->getResult();
    }
}
