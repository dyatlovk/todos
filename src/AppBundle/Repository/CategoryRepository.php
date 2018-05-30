<?php

namespace AppBundle\Repository;

use Doctrine\ORM\Query\ResultSetMapping;

/**
 * CategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CategoryRepository extends \Doctrine\ORM\EntityRepository
{
    public function getUserCatsId($userId)
    {
        $repository = $this->getEntityManager();
        $query = $repository->createQueryBuilder('p')
            ->select('p.id')
            ->from('AppBundle:Category', 'p')
            ->where('p.userID = :userId')
            ->setParameter('userId', $userId)
            ->getQuery();
        $result = [];
        foreach ($query->getResult() as $item) {
            $result[] = $item['id'];
        }
        return array_values($result);
    }
}
