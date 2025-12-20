using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace bresil_burger_csharp.Models
{
    [Table("burger")]
    public class Burger
    {
        [Key]
        [Column("id_burger")]
        public int Id { get; set; }
        
        [Column("nom")]
        public string Nom { get; set; } = string.Empty;
        
        [Column("prix")]
        public decimal Prix { get; set; }
        
        [Column("description")]
        public string? Description { get; set; }
        
        [Column("ingredients")]
        public string? Ingredients { get; set; }
        
        [Column("image")]
        public string Image { get; set; } = string.Empty;
        
        [Column("archive")]
        public bool Archive { get; set; } = false;
        
        [Column("date_creation")]
        public DateTime DateCreation { get; set; } = DateTime.Now;
    }
}