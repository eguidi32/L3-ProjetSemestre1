namespace bresil_burger_csharp.Models
{
    public class LigneCommande
    {
        public int Id { get; set; }
        public int CommandeId { get; set; }
        public int? BurgerId { get; set; }
        public int? MenuId { get; set; }
        public int Quantite { get; set; } = 1;
        public decimal PrixUnitaire { get; set; }
        
        // Propriétés de navigation
        public Commande? Commande { get; set; }
        public Burger? Burger { get; set; }
        public Menu? Menu { get; set; }
    }
}