<?php

namespace App\Entity;

use App\Repository\LigneComplementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneComplementRepository::class)]
#[ORM\Table(name: 'ligne_complement')]
class LigneComplement
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id_ligne_complement', type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: LigneCommande::class, inversedBy: 'ligneComplements')]
    #[ORM\JoinColumn(name: 'id_ligne', referencedColumnName: 'id_ligne', nullable: false, onDelete: 'CASCADE')]
    private ?LigneCommande $ligneCommande = null;

    #[ORM\ManyToOne(targetEntity: Complement::class)]
    #[ORM\JoinColumn(name: 'id_complement', referencedColumnName: 'id_complement', nullable: false)]
    private ?Complement $complement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLigneCommande(): ?LigneCommande
    {
        return $this->ligneCommande;
    }

    public function setLigneCommande(?LigneCommande $ligneCommande): static
    {
        $this->ligneCommande = $ligneCommande;
        return $this;
    }

    public function getComplement(): ?Complement
    {
        return $this->complement;
    }

    public function setComplement(?Complement $complement): static
    {
        $this->complement = $complement;
        return $this;
    }
}