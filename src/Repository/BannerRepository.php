<?php

namespace Lorry\Repository;

use Doctrine\ORM\EntityRepository;
use Lorry\Model\Banner;

class BannerRepository extends EntityRepository
{

    public function getTranslatedActiveBanners($language)
    {
        //SELECT t.title, t.subtitle, t.url, b.defaultUrl FROM Lorry\Model\Banner b LEFT JOIN b.translations t WHERE t.language = :language ORDER BY b.showFrom DESC')
        $qb = $this->_em->createQueryBuilder()
            ->select('COALESCE(t.url, b.defaultUrl) as url, COALESCE(t.imageUrl, b.defaultImageUrl) as imageUrl, t.title, t.subtitle')
            ->from('Lorry\Model\Banner', 'b')
            ->leftJoin('b.translations', 't')
            ->leftJoin('b.release', 'r')
            ->where('b.visibility = :visibility')
            ->andWhere('t.language = :language')
            ->andWhere('b.release IS NULL OR (b.release = r.id AND :now >= r.published)')
            ->andWhere('b.showFrom < :now OR b.showFrom IS NULL')
            ->andWhere('b.showUntil > :now OR b.showUntil IS NULL')
            ->orderBy('b.showFrom', 'DESC')
            ->setParameter('visibility', Banner::VISIBILITY_PUBLIC)
            ->setParameter('language', $language)
            ->setParameter('now', new \DateTime());
        return $qb->getQuery()->getScalarResult();
    }
}
