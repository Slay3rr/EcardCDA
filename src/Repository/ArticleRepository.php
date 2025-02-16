<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Category;



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

public function findByTitleOrContent(string $query): array
{
    return $this->createQueryBuilder('a')
         ->where('a.Titre LIKE :query OR a.content LIKE :query')
         ->setParameter('query', '%' . $query . '%')
         ->orderBy('a.id', 'DESC')
         ->getQuery()
         ->getResult();
}

        
}
