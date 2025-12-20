using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace bresil_burger_csharp.Models
{
    [Table("utilisateur")]
    public class Client
    {
        [Key]
        [Column("id_utilisateur")]
        public int Id { get; set; }
        
        [Column("nom")]
        public string Nom { get; set; } = string.Empty;
        
        [Column("prenom")]
        public string Prenom { get; set; } = string.Empty;
        
        [Column("telephone")]
        public string Telephone { get; set; } = string.Empty;
        
        [Column("email")]
        public string Email { get; set; } = string.Empty;
        
        [Column("password")]
        public string MotDePasse { get; set; } = string.Empty;
        
        [Column("adresse")]
        public string? Adresse { get; set; }
        
        [Column("date_inscription")]
        public DateTime DateInscription { get; set; } = DateTime.UtcNow;
        
        [Column("etat")]
        public bool Etat { get; set; } = true;
        
        [Column("type_utilisateur")]
        public string TypeUtilisateur { get; set; } = "CLIENT";
    }
}