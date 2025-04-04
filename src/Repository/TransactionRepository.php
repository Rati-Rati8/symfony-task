<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }


    /**
     * Get paginated transactions for a specific account.
     */
    public function findByAccount(Account $account, int $offset = 0, int $limit = 0): array
    {
        $query =  $this->createQueryBuilder('t')
            ->where('t.fromAccount = :account OR t.toAccount = :account')
            ->setParameter('account', $account)
            ->orderBy('t.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->getQuery();

        if ($limit) {
            $query->setMaxResults($limit);
        }

        return $query->getResult();
    }

    /**
     * Count total transactions for the account.
     */
    public function countByAccount(Account $account): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.fromAccount = :account OR t.toAccount = :account')
            ->setParameter('account', $account)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
