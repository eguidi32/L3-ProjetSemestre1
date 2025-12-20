using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace bresil_burger_csharp.Models
{
    [Table("paiement")]
    public class Paiement
    {
        [Key]
        [Column("id_paiement")]
        public int Id { get; set; }
        
        [Column("id_commande")]
        public int CommandeId { get; set; }
        
        [Column("date_paiement")]
        public DateTime DatePaiement { get; set; } = DateTime.UtcNow;
        
        [Column("montant")]
        public decimal Montant { get; set; }
        
        [Column("mode_paiement")]
        public string ModePaiement { get; set; } = string.Empty;
        
        [Column("reference")]
        public string Reference { get; set; } = string.Empty;
        
        [Column("statut")]
        public string Statut { get; set; } = "EN_ATTENTE";
        
        [ForeignKey("CommandeId")]
        public Commande? Commande { get; set; }
    }
}