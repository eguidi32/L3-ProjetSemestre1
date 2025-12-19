namespace bresil_burger_csharp.Models.ViewModels
{
    public class PanierItem
    {
        public int Id { get; set; }
        public string Type { get; set; } = string.Empty; // "burger" ou "menu"
        public string Nom { get; set; } = string.Empty;
        public decimal Prix { get; set; }
        public int Quantite { get; set; } = 1;
        public string Image { get; set; } = string.Empty;
        public List<int> ComplementIds { get; set; } = new List<int>();
    }
}