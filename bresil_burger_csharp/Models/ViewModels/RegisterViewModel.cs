using System.ComponentModel.DataAnnotations;

namespace bresil_burger_csharp.Models.ViewModels
{
    public class RegisterViewModel
    {
        [Required(ErrorMessage = "Le nom est requis")]
        [StringLength(50, ErrorMessage = "Le nom ne peut pas dépasser 50 caractères")]
        public string Nom { get; set; } = string.Empty;

        [Required(ErrorMessage = "Le prénom est requis")]
        [StringLength(50, ErrorMessage = "Le prénom ne peut pas dépasser 50 caractères")]
        public string Prenom { get; set; } = string.Empty;

        [Required(ErrorMessage = "Le téléphone est requis")]
        [Phone(ErrorMessage = "Numéro de téléphone invalide")]
        public string Telephone { get; set; } = string.Empty;

        [Required(ErrorMessage = "L'email est requis")]
        [EmailAddress(ErrorMessage = "Email invalide")]
        public string Email { get; set; } = string.Empty;

        [Required(ErrorMessage = "L'adresse est requise")]
        public string Adresse { get; set; } = string.Empty;

        [Required(ErrorMessage = "Le mot de passe est requis")]
        [StringLength(100, MinimumLength = 6, ErrorMessage = "Le mot de passe doit contenir au moins 6 caractères")]
        [DataType(DataType.Password)]
        public string MotDePasse { get; set; } = string.Empty;

        [Required(ErrorMessage = "La confirmation du mot de passe est requise")]
        [Compare("MotDePasse", ErrorMessage = "Les mots de passe ne correspondent pas")]
        [DataType(DataType.Password)]
        public string ConfirmMotDePasse { get; set; } = string.Empty;
    }
}