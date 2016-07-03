<?php

namespace Dada\AdvertisementBundle\Repository;

/**
 * AdvertisementRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AdvertisementRepository extends \Doctrine\ORM\EntityRepository{

    /**
     * Retuns all items in the given page for the given user
     * @param $page int Page number
     * @param $user User User object
     * @param $nbItems int Nb items to show on a single page
     * @return array
     */
    public function findByPageAndUser($page, $user, $nbItems){
        $query = $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->setFirstResult((($page-1)*$nbItems))
            ->setMaxResults($nbItems)
            ->addOrderBy('a.public', 'desc')
            ->addOrderBy('a.published', 'asc');
        return $query->getQuery()->getResult();
    }

    /**
     * Return $nb last adverts
     * @param $nb int number of results to return
     * @return array
     */
    public function findLast($nb){
        if(!is_numeric($nb) && $nb > 0)
            throw new \InvalidArgumentException('An integer value was expected');
        $query = $this->createQueryBuilder('a')
            ->where('a.public = true')
            ->orderBy('a.published', 'desc')
            ->setMaxResults(6);
        return $query->getQuery()->getResult();
    }

    /**
     * Return number of pages
     *
     * @param $user User
     * @param $nbItems int Number of items per page
     * @return int number of pages
     */
    public function findPageCount($user, $nbItems){
        $query = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.user = :user')
            ->setParameter('user', $user);
        $result = $query->getQuery()->getSingleScalarResult();
        return ceil($result/$nbItems);
    }

    /**
     * Return all entries published > to $maxDays
     *
     * @param $maxDays int number of days after what Adevrts are automatically unpublished
     * @return array Advertisemenst
     */
    public function cleanOldEntries($maxDays){
        $time = new \DateTime();
        $interval = new \DateInterval('P'.$maxDays.'D');
        $time->sub($interval);
        $query = $this->createQueryBuilder('a')
            ->where('a.public = true')
            ->andWhere('a.published < :time')
            ->setParameter('time', $time);
        return $query->getQuery()->getResult();
    }

}
