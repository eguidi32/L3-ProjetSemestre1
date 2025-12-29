<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findGestionnaires(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.typeUtilisateur = :type')
            ->setParameter('type', 'GESTIONNAIRE')
            ->getQuery()
            ->getResult();
    }

    public function findLivreurs(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.typeUtilisateur = :type')
            ->setParameter('type', 'LIVREUR')
            ->getQuery()
            ->getResult();
    }

    public function findLivreursDisponibles(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.typeUtilisateur = :type')
            ->andWhere('u.disponible = true')
            ->andWhere('u.etat = true')
            ->setParameter('type', 'LIVREUR')
            ->getQuery()
            ->getResult();
    }

    public function findClients(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.typeUtilisateur = :type')
            ->setParameter('type', 'CLIENT')
            ->getQuery()
            ->getResult();
    }
}