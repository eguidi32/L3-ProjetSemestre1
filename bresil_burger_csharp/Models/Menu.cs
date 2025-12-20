using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace bresil_burger_csharp.Models
{
    [Table("menu")]
    public class Menu
    {
        [Key]
        [Column("id_menu")]
        public int Id { get; set; }
        
        [Column("nom")]
        public string Nom { get; set; } = string.Empty;
        
        [Column("description")]
        public string? Description { get; set; }
        
        [Column("image")]
        public string Image { get; set; } = string.Empty;
        
        [Column("archive")]
        public bool Archive { get; set; } = false;
        
        [Column("date_creation")]
        public DateTime DateCreation { get; set; } = DateTime.Now;
        
        [Column("id_burger")]
        public int BurgerId { get; set; }
        
        [Column("id_boisson")]
        public int BoissonId { get; set; }
        
        [Column("id_frites")]
        public int FriteId { get; set; }
        
        [ForeignKey("BurgerId")]
        public Burger? Burger { get; set; }
        
        [ForeignKey("BoissonId")]
        public Complement? Boisson { get; set; }
        
        [ForeignKey("FriteId")]
        public Complement? Frite { get; set; }
        
        [NotMapped]
        public decimal Prix => (Burger?.Prix ?? 0) + (Boisson?.Prix ?? 0) + (Frite?.Prix ?? 0);
    }
}