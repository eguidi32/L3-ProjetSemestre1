namespace bresil_burger_csharp.Models
{
    public class Paiement
    {
        public int Id { get; set; }
        public int CommandeId { get; set; }
        public DateTime DatePaiement { get; set; } = DateTime.Now;
        public decimal Montant { get; set; }
        public string ModePaiement { get; set; } = string.Empty; // Wave, OM (Orange Money)
        public string Reference { get; set; } = string.Empty;
        
        // Propriété de navigation
        public Commande? Commande { get; set; }
    }
}