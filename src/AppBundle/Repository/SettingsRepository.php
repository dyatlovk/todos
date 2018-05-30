<?php

namespace AppBundle\Repository;

/**
 * SettingsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SettingsRepository extends \Doctrine\ORM\EntityRepository
{
    public function getUserSettingsId($userId)
    {
        $repository = $this->getEntityManager();
        $query = $repository->createQueryBuilder('p')
            ->select('p.id')
            ->from('AppBundle:Settings', 'p')
            ->where('p.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery();
        $result = [];
        foreach ($query->getResult() as $item) {
            $result[] = $item['id'];
        }
        return array_values($result);
    }
}