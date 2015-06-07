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
            ->setParameter('now', new \DateTime())
            ->setParameter('game', $game);
        return $qb->getQuery()->getResult();
    }

    public function getPublishedByOwner($owner)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('a')
            ->from('Lorry\Model\Addon', 'a')
            ->leftJoin('a.latestRelease', 'r')
            ->where('a.owner = :owner')
            ->andWhere(':now >= r.published')
            ->setParameter('now', new \DateTime())
            ->setParameter('owner', $owner);
        return $qb->getQuery()->getResult();
    }

    public function getOwnedByTitleAndGame($owner, $title, $game)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('a')
            ->from('Lorry\Model\Addon', 'a')
            ->leftJoin('a.latestRelease', 'r')
            ->leftJoin('a.game', 'g')
            ->leftJoin('a.translations', 't')
            ->where('a.owner = :owner')
            ->andWhere('a.game = :game')
            ->andWhere('t.title = :title')
            ->setParameter('owner', $owner)
            ->setParameter('title', $title)
            ->setParameter('game', $game);
        return $qb->getQuery()->getResult();
    }
}
