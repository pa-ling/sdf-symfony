<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class HomeRepository extends EntityRepository
{
    //bsp des aufbaus
    /*
    public function findAllOrderedByName()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT d FROM AppBundle:Document d ORDER BY d.name ASC'
            )
            ->getResult();
    }*/
}