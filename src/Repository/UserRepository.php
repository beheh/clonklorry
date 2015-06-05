<?php

namespace Lorry\Repository;

use Doctrine\ORM\EntityRepository;
use Lorry\Model\User;

class UserRepository extends EntityRepository
{

    public function getAllAdministrators($maxResults = null, $firstResult = null)
    {
        return $this->_em->createQuery('SELECT u FROM Lorry\Model\User u WHERE u.permissions = :permission')
                ->setParameter('permission', User::PERMISSION_ADMINISTRATE)
                ->setMaxResults($maxResults)
                ->setFirstResult($firstResult)
                ->getResult();
    }

    public function getAllModerators($maxResults = null, $firstResult = null)
    {
        return $this->_em->createQuery('SELECT u FROM Lorry\Model\User u WHERE u.permissions = :permission')
                ->setParameter('permission', User::PERMISSION_MODERATE)
                ->setMaxResults($maxResults)
                ->setFirstResult($firstResult)
                ->getResult();
    }

    public function getAll($maxResults = null, $firstResult = null)
    {
        return $this->_em->createQuery('SELECT u FROM Lorry\Model\User u')
                ->setMaxResults($maxResults)
                ->setFirstResult($firstResult)
                ->getResult();
    }
}
