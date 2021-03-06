<?php

namespace AppBundle\Repository;

/**
 * TodosRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TodosRepository extends \Doctrine\ORM\EntityRepository
{
    public function getUserTodosId($userId)
    {
        $repository = $this->getEntityManager();
        $query = $repository->createQueryBuilder('p')
            ->select('p.id')
            ->from('AppBundle:Todos', 'p')
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
