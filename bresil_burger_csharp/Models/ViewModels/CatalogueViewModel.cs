namespace bresil_burger_csharp.Models.ViewModels
{
    public class CatalogueViewModel
    {
        public List<Burger> Burgers { get; set; } = new List<Burger>();
        public List<Menu> Menus { get; set; } = new List<Menu>();
        public List<Complement> Complements { get; set; } = new List<Complement>();
        public string Filtre { get; set; } = "tous"; // tous, burger, menu
    }
}