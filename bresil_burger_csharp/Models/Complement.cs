namespace bresil_burger_csharp.Models
{
    public class Complement
    {
        public int Id { get; set; }
        public string Nom { get; set; } = string.Empty;
        public decimal Prix { get; set; }
        public string Image { get; set; } = string.Empty;
        public string Type { get; set; } = string.Empty; // "boisson" ou "frite"
        public bool Archive { get; set; } = false;
    }
}