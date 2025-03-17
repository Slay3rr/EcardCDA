<?php
namespace App\Repository;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.Category', 'c')
            ->andWhere('c.id = :categoryId')
            ->setParameter('categoryId', $category->getId())
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(50)  // Limite le nombre de résultats
            ->getQuery()
            ->getResult();
    }

    public function findByTitleOrContent(string $query): array
    {
        // Nettoyage de la requête
        $searchTerm = trim(strip_tags($query));
        

        // Échappement des caractères spéciaux pour LIKE
        $searchTerm = $this->escapeLikeString($searchTerm);

        return $this->createQueryBuilder('a')
            ->where('LOWER(a.Titre) LIKE LOWER(:query)')
            ->orWhere('LOWER(a.content) LIKE LOWER(:query)')
            ->setParameter('query', '%' . $searchTerm . '%')
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(50)  // Limite le nombre de résultats
            ->getQuery()
            ->getResult();
    }

    private function escapeLikeString(string $str): string
    {
        // Échappe les caractères spéciaux LIKE
        return str_replace(['%', '_'], ['\%', '\_'], $str);
    }
}