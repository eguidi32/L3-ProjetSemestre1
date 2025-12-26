<?php
public function findWithFilters(
    ?string $etat, 
    ?string $typeService, 
    ?\DateTime $dateDebut, 
    ?\DateTime $dateFin, 
    ?int $clientId,
    ?int $burgerId = null,
    ?int $menuId = null
): array {
    $qb = $this->createQueryBuilder('c')
        ->leftJoin('c.lignesCommande', 'lc')
        ->orderBy('c.dateCommande', 'DESC');

    if ($etat) {
        $qb->andWhere('c.etat = :etat')->setParameter('etat', $etat);
    }

    if ($typeService) {
        $qb->andWhere('c.typeService = :typeService')->setParameter('typeService', $typeService);
    }

    if ($dateDebut) {
        $qb->andWhere('c.dateCommande >= :dateDebut')->setParameter('dateDebut', $dateDebut);
    }

    if ($dateFin) {
        $dateFin->setTime(23, 59, 59);
        $qb->andWhere('c.dateCommande <= :dateFin')->setParameter('dateFin', $dateFin);
    }

    if ($clientId) {
        $qb->andWhere('c.client = :clientId')->setParameter('clientId', $clientId);
    }

    if ($burgerId) {
        $qb->andWhere('lc.typeProduit = :typeBurger')
           ->andWhere('lc.burger = :burgerId')
           ->setParameter('typeBurger', 'BURGER')
           ->setParameter('burgerId', $burgerId);
    }

    if ($menuId) {
        $qb->andWhere('lc.typeProduit = :typeMenu')
           ->andWhere('lc.menu = :menuId')
           ->setParameter('typeMenu', 'MENU')
           ->setParameter('menuId', $menuId);
    }

    return $qb->distinct()->getQuery()->getResult();
}