namespace bresil_burger_csharp.Models
{
    public class Burger
    {
        public int Id { get; set; }
        public string Nom { get; set; } = string.Empty;
        public decimal Prix { get; set; }
        public string Image { get; set; } = string.Empty;
        public bool Archive { get; set; } = false;
        public DateTime DateCreation { get; set; } = DateTime.Now;
    }
}