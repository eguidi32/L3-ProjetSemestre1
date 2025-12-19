namespace bresil_burger_csharp.Models
{
    public class Menu
    {
        public int Id { get; set; }
        public string Nom { get; set; } = string.Empty;
        public string Image { get; set; } = string.Empty;
        public bool Archive { get; set; } = false;
        public int BurgerId { get; set; }
        public int BoissonId { get; set; }
        public int FriteId { get; set; }
        
        // Propriétés de navigation
        public Burger? Burger { get; set; }
        public Complement? Boisson { get; set; }
        public Complement? Frite { get; set; }
        
        // Prix calculé (somme des composants)
        public decimal Prix => (Burger?.Prix ?? 0) + (Boisson?.Prix ?? 0) + (Frite?.Prix ?? 0);
    }
}