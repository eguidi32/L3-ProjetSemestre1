namespace bresil_burger_csharp.Models
{
    public class Commande
    {
        public int Id { get; set; }
        public DateTime DateCommande { get; set; } = DateTime.Now;
        public string Etat { get; set; } = "en_attente"; // en_attente, validee, en_preparation, terminee, annulee
        public string TypeLivraison { get; set; } = string.Empty; // sur_place, a_emporter, livraison
        public decimal MontantTotal { get; set; }
        public int ClientId { get; set; }
        public int? ZoneId { get; set; }
        
        // Propriétés de navigation
        public Client? Client { get; set; }
        public Zone? Zone { get; set; }
        public List<LigneCommande> LignesCommande { get; set; } = new List<LigneCommande>();
        public Paiement? Paiement { get; set; }
    }
}