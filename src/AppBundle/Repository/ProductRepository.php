<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository
{

    public function searchByCategoryAndDescription($keyword)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM AppBundle:Product p 
                        where p.category like \'%' . $keyword . '%\' 
                        or p.description like \'%' . $keyword . '%\''
            )
            ->getResult();
    }


}

