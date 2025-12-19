namespace bresil_burger_csharp.Models
{
    public class LigneCommandeComplement
    {
        public int Id { get; set; }
        public int LigneCommandeId { get; set; }
        public int ComplementId { get; set; }
        public int Quantite { get; set; } = 1;
        
        // Propriétés de navigation
        public LigneCommande? LigneCommande { get; set; }
        public Complement? Complement { get; set; }
    }
}