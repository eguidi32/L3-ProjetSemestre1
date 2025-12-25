<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name: 'commande')]
class Commande
{
    public const ETAT_EN_ATTENTE = 'EN_ATTENTE';
    public const ETAT_EN_PREPARATION = 'EN_PREPARATION';
    public const ETAT_PRETE = 'PRETE';
    public const ETAT_TERMINEE = 'TERMINEE';
    public const ETAT_ANNULEE = 'ANNULEE';

    public const TYPE_SUR_PLACE = 'SUR_PLACE';
    public const TYPE_A_EMPORTER = 'A_EMPORTER';
    public const TYPE_LIVRAISON = 'LIVRAISON';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id_commande', type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $numero = null;

    #[ORM\Column(name: 'date_commande', type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $dateCommande = null;

    #[ORM\Column(name: 'type_service', length: 20)]
    private ?string $typeService = null;

    #[ORM\Column(length: 20, options: ['default' => 'EN_ATTENTE'])]
    private ?string $etat = 'EN_ATTENTE';

    #[ORM\Column(name: 'montant_total', type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => 0])]
    private ?string $montantTotal = '0';

    #[ORM\Column(name: 'adresse_livraison', type: Types::TEXT, nullable: true)]
    private ?string $adresseLivraison = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_client', referencedColumnName: 'id_utilisateur', nullable: false)]
    private ?Utilisateur $client = null;

    #[ORM\ManyToOne(targetEntity: Zone::class)]
    #[ORM\JoinColumn(name: 'id_zone', referencedColumnName: 'id_zone', nullable: true)]
    private ?Zone $zone = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_livreur', referencedColumnName: 'id_utilisateur', nullable: true)]
    private ?Utilisateur $livreur = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: LigneCommande::class, cascade: ['persist', 'remove'])]
    private Collection $lignesCommande;

    #[ORM\OneToOne(mappedBy: 'commande', targetEntity: Paiement::class, cascade: ['persist', 'remove'])]
    private ?Paiement $paiement = null;

    public function __construct()
    {
        $this->lignesCommande = new ArrayCollection();
        $this->dateCommande = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;
        return $this;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeInterface $dateCommande): static
    {
        $this->dateCommande = $dateCommande;
        return $this;
    }

    public function getTypeService(): ?string
    {
        return $this->typeService;
    }

    public function setTypeService(string $typeService): static
    {
        $this->typeService = $typeService;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;
        return $this;
    }

    public function getMontantTotal(): ?string
    {
        return $this->montantTotal;
    }

    public function setMontantTotal(string $montantTotal): static
    {
        $this->montantTotal = $montantTotal;
        return $this;
    }

    public function getAdresseLivraison(): ?string
    {
        return $this->adresseLivraison;
    }

    public function setAdresseLivraison(?string $adresseLivraison): static
    {
        $this->adresseLivraison = $adresseLivraison;
        return $this;
    }

    public function getClient(): ?Utilisateur
    {
        return $this->client;
    }

    public function setClient(?Utilisateur $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): static
    {
        $this->zone = $zone;
        return $this;
    }

    public function getLivreur(): ?Utilisateur
    {
        return $this->livreur;
    }

    public function setLivreur(?Utilisateur $livreur): static
    {
        $this->livreur = $livreur;
        return $this;
    }

    public function getLignesCommande(): Collection
    {
        return $this->lignesCommande;
    }

    public function addLigneCommande(LigneCommande $ligneCommande): static
    {
        if (!$this->lignesCommande->contains($ligneCommande)) {
            $this->lignesCommande->add($ligneCommande);
            $ligneCommande->setCommande($this);
        }
        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): static
    {
        if ($this->lignesCommande->removeElement($ligneCommande)) {
            if ($ligneCommande->getCommande() === $this) {
                $ligneCommande->setCommande(null);
            }
        }
        return $this;
    }

    public function getPaiement(): ?Paiement
    {
        return $this->paiement;
    }

    public function setPaiement(?Paiement $paiement): static
    {
        if ($paiement !== null && $paiement->getCommande() !== $this) {
            $paiement->setCommande($this);
        }
        $this->paiement = $paiement;
        return $this;
    }

    public function isPaye(): bool
    {
        return $this->paiement !== null && $this->paiement->getStatut() === 'VALIDE';
    }

    public function isLivraison(): bool
    {
        return $this->typeService === self::TYPE_LIVRAISON;
    }

    public function getTypeServiceLabel(): string
    {
        return match($this->typeService) {
            self::TYPE_SUR_PLACE => 'Sur place',
            self::TYPE_A_EMPORTER => 'À emporter',
            self::TYPE_LIVRAISON => 'Livraison',
            default => $this->typeService
        };
    }

    public function getEtatLabel(): string
    {
        return match($this->etat) {
            self::ETAT_EN_ATTENTE => 'En attente',
            self::ETAT_EN_PREPARATION => 'En préparation',
            self::ETAT_PRETE => 'Prête',
            self::ETAT_TERMINEE => 'Terminée',
            self::ETAT_ANNULEE => 'Annulée',
            default => $this->etat
        };
    }

    public function getEtatBadgeClass(): string
    {
        return match($this->etat) {
            self::ETAT_EN_ATTENTE => 'bg-warning',
            self::ETAT_EN_PREPARATION => 'bg-info',
            self::ETAT_PRETE => 'bg-primary',
            self::ETAT_TERMINEE => 'bg-success',
            self::ETAT_ANNULEE => 'bg-danger',
            default => 'bg-secondary'
        };
    }
}