using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace bresil_burger_csharp.Models
{
    [Table("ligne_complement")]
    public class LigneCommandeComplement
    {
        [Key]
        [Column("id_ligne_complement")]
        public int Id { get; set; }
        
        [Column("id_ligne")]
        public int LigneCommandeId { get; set; }
        
        [Column("id_complement")]
        public int ComplementId { get; set; }
        
        [ForeignKey("LigneCommandeId")]
        public LigneCommande? LigneCommande { get; set; }
        
        [ForeignKey("ComplementId")]
        public Complement? Complement { get; set; }
    }
}