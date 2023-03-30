<?php

namespace App\Repository;

use App\Class\filtre;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

//    public function filtre($idSite){
//
//        // filtre sur site
//
//        $queryBuilder = $this->createQueryBuilder('s');
//
//
//        $queryBuilder -> andWhere('s.site');
//    }
    public function findByFiltre(filtre $filtre, $userConnecte) {
        $dateJour = new \DateTime();
        $query = $this
            ->createQueryBuilder('sorties')
            ->select('sites','sorties')
            ->join('sorties.site','sites'); //sorties.site correspond au champ "site" de sorties


        if (!empty($filtre->site)){
            $query = $query
                ->andWhere('sites.id = (:site)')
                ->setParameter('site', $filtre->site->getId());
        }

        if (!empty($filtre->global)){
            $query = $query
                ->andWhere('sorties.nom like :global')
                ->setParameter('global', "%{$filtre->global}%");
        }

        if (!empty($filtre->dateDebut)&&!empty($filtre->dateFin)){
            $query = $query
                ->andWhere('sorties.dateHeureDebut >= :dateDebut')
                ->andWhere('sorties.dateHeureDebut <= :dateFin')
                ->setParameter('dateDebut', $filtre->dateDebut)
                ->setParameter('dateFin', $filtre->dateFin);
        }

        if (!empty($filtre->organisateur)){
            $query = $query
                ->andWhere('sorties.organisateur = :organisateur')
                ->setParameter('organisateur', $userConnecte);
        }

        if (!empty($filtre->inscrit)){
            $query = $query
                ->join('sorties.inscriptions','sortieIns')
                ->andWhere('sortieIns.participant = :inscrit')
                ->setParameter('inscrit', $userConnecte);
        }

        if (!empty($filtre->pasInscrit)){
            $query = $query
                ->join('sorties.inscriptions','sortieIns')
                ->andWhere('sortieIns.participant != :pasInscrit')
                ->setParameter('pasInscrit', $userConnecte);
        }

        if (!empty($filtre->sortiePassee)){
            $query = $query
                ->andWhere('sorties.dateHeureDebut < :dateJour')
                ->setParameter('dateJour', $dateJour);
        }


        return $query->getQuery()->getResult();
    }


}
