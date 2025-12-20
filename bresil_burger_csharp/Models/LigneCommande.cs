using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace bresil_burger_csharp.Models
{
    [Table("ligne_commande")]
    public class LigneCommande
    {
        [Key]
        [Column("id_ligne")]
        public int Id { get; set; }
        
        [Column("id_commande")]
        public int CommandeId { get; set; }
        
        [Column("id_burger")]
        public int? BurgerId { get; set; }
        
        [Column("id_menu")]
        public int? MenuId { get; set; }
        
        [Column("quantite")]
        public int Quantite { get; set; } = 1;
        
        [Column("prix_unitaire")]
        public decimal PrixUnitaire { get; set; }
        
        [Column("sous_total")]
        public decimal SousTotal { get; set; }
        
        [Column("type_produit")]
        public string TypeProduit { get; set; } = string.Empty;
        
        [ForeignKey("CommandeId")]
        public Commande? Commande { get; set; }
        
        [ForeignKey("BurgerId")]
        public Burger? Burger { get; set; }
        
        [ForeignKey("MenuId")]
        public Menu? Menu { get; set; }
    }
}