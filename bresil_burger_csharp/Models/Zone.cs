namespace bresil_burger_csharp.Models
{
    public class Zone
    {
        public int Id { get; set; }
        public string Nom { get; set; } = string.Empty;
        public decimal PrixLivraison { get; set; }
        public string Quartiers { get; set; } = string.Empty; // Liste des quartiers séparés par virgule
    }
}