using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace bresil_burger_csharp.Models
{
    [Table("zone")]
    public class Zone
    {
        [Key]
        [Column("id_zone")]
        public int Id { get; set; }
        
        [Column("nom")]
        public string Nom { get; set; } = string.Empty;
        
        [Column("prix_livraison")]
        public decimal PrixLivraison { get; set; }
        
        [Column("quartiers")]
        public string Quartiers { get; set; } = string.Empty;
    }
}