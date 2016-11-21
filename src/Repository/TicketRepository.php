<?php

namespace Lorry\Repository;

use Doctrine\ORM\EntityRepository;

class TicketRepository extends EntityRepository
{
    public function getNewTickets()
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('t')
            ->from('Lorry\Model\Ticket', 't')
            ->where('t.assigned IS null')
            ->orderBy('t.submitted', 'ASC');
         return $qb->getQuery()->getResult();
    }
}
