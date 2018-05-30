<?php
namespace AppBundle\Models;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\User as BaseUser;

/**
 *
 */
class TodosModel
{
    private $em;

    const LIMIT = 20;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Find all todos
     *
     * @param  integer $status [description]
     * @param  [type]  $userId [description]
     * @return [type]          [description]
     */
    public function findAll($status = 1, $userId, $limit = self::LIMIT)
    {
        if(!isset($userId) || empty($userId)) return false;

        $cats  = $this->getCats($status, $userId);
        $todos = $this->getTodos(
                                    $status,
                                    $userId,
                                    $catId   = $cats[0]['id'],
                                    $orderby = 'title',
                                    $sort    = 'desc',
                                    $limit
                                );

        return ['cats'=>$cats, 'todos' => $todos];
    }

    /**
     * [getCats description]
     * @param  [type] $status [description]
     * @param  [type] $userId [description]
     * @return [type]         [description]
     */
    public function getCats($status, $userId)
    {
        $sql = "
            SELECT
                COUNT(t.id) AS count, c.title, c.id
            FROM
                category c
            LEFT JOIN
                todos t
            ON
                t.cat_id = c.id
            WHERE
                c.status = :status
            AND
                c.user_id = :userId
            GROUP BY
                c.id;
        ";

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('status', $status);
        $stmt->bindValue('userId', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * [getTodos description]
     * @param  [type] $status  [description]
     * @param  [type] $userId  [description]
     * @param  [type] $limit   [description]
     * @param  [type] $cat     [description]
     * @param  [type] $orderby [description]
     * @param  [type] $sort    [description]
     * @return [type]          [description]
     */
    public function getTodos(
                                $status  = 1,
                                $userId,
                                $catId,
                                $orderby = 'title',
                                $sort    = 'asc',
                                $limit   = self::LIMIT
                            )
    {
        $sql = "
            SELECT
                t.id,t.title,t.content,
                t.date_sheduled AS dateSheduled,
                t.date_create AS dateCreate,
                t.date_modify AS dateModify
            FROM todos t
            WHERE t.cat_id = :catId
            AND t.user_id = :userId
            AND t.status = :status
            ORDER BY $orderby $sort
            LIMIT $limit
        ";

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('catId', $catId);
        $stmt->bindValue('userId', $userId);
        $stmt->bindValue('status', $status);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * [todosList description]
     * @return [type] [description]
     */
    public function todosList($userId)
    {
        $sql = "
            SELECT
                t.id,
                t.title,
                t.content,
                t.date_create AS dateCreate ,
                t.date_sheduled AS dateSheduled,
                t.date_modify AS dateModify,
                c.title AS catTitle
            FROM todos t
            LEFT JOIN category c
            ON t.cat_id = c.id
            WHERE t.user_id = :userId
            ORDER BY c.title
            LIMIT 20
        ";
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->bindValue('userId', $userId);
    $stmt->execute();
    return $stmt->fetchAll();
    }
}
