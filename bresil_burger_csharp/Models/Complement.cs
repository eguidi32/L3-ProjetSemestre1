using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace bresil_burger_csharp.Models
{
    [Table("complement")]
    public class Complement
    {
        [Key]
        [Column("id_complement")]
        public int Id { get; set; }
        
        [Column("nom")]
        public string Nom { get; set; } = string.Empty;
        
        [Column("prix")]
        public decimal Prix { get; set; }
        
        [Column("image")]
        public string Image { get; set; } = string.Empty;
        
        [Column("type_complement")]
        public string Type { get; set; } = string.Empty;
        
        [Column("archive")]
        public bool Archive { get; set; } = false;
        
        [Column("date_creation")]
        public DateTime DateCreation { get; set; } = DateTime.Now;
    }
}