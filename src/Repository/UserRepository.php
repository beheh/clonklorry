<?php

namespace Lorry\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{

    public function getAllAdministrators($firstResult = null, $maxResults = null)
    {
        return $this->_em->createQuery('SELECT u FROM Lorry\Model\User u WHERE u.permissions = :permission')
                ->setParameter('permission', User::PERMISSION_ADMINISTRATE)
                ->setFirstResult($firstResult)
                ->setMaxResults($maxResults)
                ->getResult();
    }

    public function getAllModerators($firstResult = null, $maxResults = null)
    {
        return $this->_em->createQuery('SELECT u FROM Lorry\Model\User u WHERE u.permissions = :permission')
                ->setParameter('permission', User::PERMISSION_MODERATE)
                ->setFirstResult($firstResult)
                ->setMaxResults($maxResults)
                ->getResult();
    }
}
