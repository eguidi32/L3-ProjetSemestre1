namespace bresil_burger_csharp.Models.ViewModels
{
    public class PanierViewModel
    {
        public List<PanierItem> Items { get; set; } = new List<PanierItem>();
        public decimal Total => Items.Sum(i => i.Prix * i.Quantite);
        public int NombreArticles => Items.Sum(i => i.Quantite);
    }
}