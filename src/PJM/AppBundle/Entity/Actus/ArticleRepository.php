<?php

namespace PJM\AppBundle\Entity\Actus;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ArticleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleRepository extends EntityRepository
{
    public function getArticles($nombreParPage = 5, $page = 1, $estPublie = true)
    {
        if ($page < 1) {
            throw new
                \InvalidArgumentException('L\'argument $page ne peut être inférieur à 1 (valeur : "'.$page.'").');
        }

        $query = $this->createQueryBuilder('a')
                    ->andWhere('a.publication = '.$estPublie)
                    ->leftJoin('a.categories', 'c')
                    ->addSelect('c')
                    ->orderBy('a.date', 'DESC')
                    ->getQuery();


        $query->setFirstResult(($page-1) * $nombreParPage) // on définit l'article à partir duquel commencer la liste
              ->setMaxResults($nombreParPage); // ainsi que le nombre d'articles à afficher

        return new Paginator($query);
    }
}
