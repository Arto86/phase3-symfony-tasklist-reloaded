<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findByUserOrderedByStatus(User $user): array
    {

        return $this->createQueryBuilder('t')
            ->where('t.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('t.isPinned', 'DESC')
            ->addOrderBy('CASE 
                WHEN t.status = \'Pending\' THEN 1 
                WHEN t.status = \'Completed\' THEN 2 
                ELSE 3 END', 'ASC')
            ->addOrderBy('t.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function findByFilters(User $user, ?string $status, ?string $priority): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.owner = :user')
            ->setParameter('user', $user);

        // Si un statut est sélectionné, on filtre
        if (!empty($status)) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        // Si une priorité est sélectionnée, on filtre
        if (!empty($priority)) {
            $qb->andWhere('t.priority = :priority')
                ->setParameter('priority', $priority);
        }

        // On garde notre tri de base (Épinglé > Statut > Priorité)
        return $qb->orderBy('t.isPinned', 'DESC')
            ->addOrderBy("CASE WHEN t.status = 'en cours' THEN 1 ELSE 2 END", "ASC")
            ->addOrderBy('t.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Task[] Returns an array of Task objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Task
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
