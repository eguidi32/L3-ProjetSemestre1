<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ORM\Table(name: 'menu')]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id_menu', type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $archive = false;

    #[ORM\Column(name: 'date_creation', type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: Burger::class)]
    #[ORM\JoinColumn(name: 'id_burger', referencedColumnName: 'id_burger', nullable: false)]
    private ?Burger $burger = null;

    #[ORM\ManyToOne(targetEntity: Complement::class)]
    #[ORM\JoinColumn(name: 'id_boisson', referencedColumnName: 'id_complement', nullable: false)]
    private ?Complement $boisson = null;

    #[ORM\ManyToOne(targetEntity: Complement::class)]
    #[ORM\JoinColumn(name: 'id_frites', referencedColumnName: 'id_complement', nullable: false)]
    private ?Complement $frites = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function isArchive(): ?bool
    {
        return $this->archive;
    }

    public function setArchive(bool $archive): static
    {
        $this->archive = $archive;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getBurger(): ?Burger
    {
        return $this->burger;
    }

    public function setBurger(?Burger $burger): static
    {
        $this->burger = $burger;
        return $this;
    }

    public function getBoisson(): ?Complement
    {
        return $this->boisson;
    }

    public function setBoisson(?Complement $boisson): static
    {
        $this->boisson = $boisson;
        return $this;
    }

    public function getFrites(): ?Complement
    {
        return $this->frites;
    }

    public function setFrites(?Complement $frites): static
    {
        $this->frites = $frites;
        return $this;
    }

    // Prix calculé = burger + boisson + frites
    public function getPrix(): string
    {
        $prix = 0;
        if ($this->burger) {
            $prix += (float) $this->burger->getPrix();
        }
        if ($this->boisson) {
            $prix += (float) $this->boisson->getPrix();
        }
        if ($this->frites) {
            $prix += (float) $this->frites->getPrix();
        }
        return number_format($prix, 2, '.', '');
    }
}