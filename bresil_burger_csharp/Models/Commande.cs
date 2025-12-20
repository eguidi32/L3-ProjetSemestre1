using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace bresil_burger_csharp.Models
{
    [Table("commande")]
    public class Commande
    {
        [Key]
        [Column("id_commande")]
        public int Id { get; set; }
        
        [Column("numero")]
        public string Numero { get; set; } = string.Empty;
        
        [Column("date_commande")]
        public DateTime DateCommande { get; set; } = DateTime.UtcNow;
        
        [Column("etat")]
        public string Etat { get; set; } = "EN_ATTENTE";
        
        [Column("type_service")]
        public string TypeLivraison { get; set; } = string.Empty;
        
        [Required]
        [Column("montant_total")]
        public decimal MontantTotal { get; set; } = 0;
        
        [Column("adresse_livraison")]
        public string? AdresseLivraison { get; set; }
        
        [Column("id_client")]
        public int ClientId { get; set; }
        
        [Column("id_zone")]
        public int? ZoneId { get; set; }
        
        [Column("id_livreur")]
        public int? LivreurId { get; set; }
        
        [ForeignKey("ClientId")]
        public Client? Client { get; set; }
        
        [ForeignKey("ZoneId")]
        public Zone? Zone { get; set; }
        
        public List<LigneCommande> LignesCommande { get; set; } = new List<LigneCommande>();
        public Paiement? Paiement { get; set; }
    }
}