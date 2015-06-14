<?php

namespace Lorry\Repository;

use Doctrine\ORM\EntityRepository;
use Lorry\Model\Banner;
use \DateTime;

class BannerRepository extends EntityRepository
{

    public function getTranslatedActiveBanners($language)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('COALESCE(t.url, b.defaultUrl) as url, i.guid as image_guid, t.title, t.subtitle')
            ->from('Lorry\Model\Banner', 'b')
            ->leftJoin('b.translations', 't')
            ->leftJoin('b.release', 'r')
            ->leftJoin('Lorry\Model\Image', 'i', \Doctrine\ORM\Query\Expr\Join::WITH, 'i.id = COALESCE(IDENTITY(t.image), IDENTITY(b.defaultImage))')
            ->where('b.visibility = :visibility')
            ->andWhere('t.language = :language')
            ->andWhere('b.release IS NULL OR (b.release = r.id AND :now >= r.published)')
            ->andWhere('b.showFrom < :now OR b.showFrom IS NULL')
            ->andWhere('b.showUntil > :now OR b.showUntil IS NULL')
            ->orderBy('b.showFrom', 'DESC')
            ->setParameter('visibility', Banner::VISIBILITY_PUBLIC)
            ->setParameter('language', $language)
            ->setParameter('now', new DateTime());
        return $qb->getQuery()->getScalarResult();
    }
}
