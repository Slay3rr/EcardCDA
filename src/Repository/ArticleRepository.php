<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

       /**
        * @param Category $category
        * @return Article[]
        */
        public function findByCategory(Category $category): array
        {
            return $this->createQueryBuilder('a')
                ->innerJoin('a.Category', 'c')
                ->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $category->getId())
                ->orderBy('a.id', 'DESC')
                ->getQuery()
                ->getResult();
        }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
