using System.ComponentModel.DataAnnotations;

namespace bresil_burger_csharp.Models.ViewModels
{
    public class ValidationCommandeViewModel
    {
        public PanierViewModel Panier { get; set; } = new PanierViewModel();
        public List<Zone> Zones { get; set; } = new List<Zone>();

        [Required(ErrorMessage = "Veuillez choisir un type de livraison")]
        public string TypeLivraison { get; set; } = string.Empty; // sur_place, a_emporter, livraison

        public int? ZoneId { get; set; }

        public decimal FraisLivraison { get; set; } = 0;

        public decimal TotalCommande => Panier.Total + FraisLivraison;

        // Adresse de livraison
        public string? AdresseEnregistree { get; set; } // Adresse du client depuis la base de données
        public string? NouvelleAdresse { get; set; } // Nouvelle adresse saisie
        public bool UtiliserAdresseEnregistree { get; set; } = true; // Choix de l'utilisateur
        public string AdresseLivraison => UtiliserAdresseEnregistree ? (AdresseEnregistree ?? string.Empty) : (NouvelleAdresse ?? string.Empty);
    }
}