using System.ComponentModel.DataAnnotations;

namespace bresil_burger_csharp.Models.ViewModels
{
    public class LoginViewModel
    {
        [Required(ErrorMessage = "L'email est requis")]
        [EmailAddress(ErrorMessage = "Email invalide")]
        public string Email { get; set; } = string.Empty;

        [Required(ErrorMessage = "Le mot de passe est requis")]
        [DataType(DataType.Password)]
        public string MotDePasse { get; set; } = string.Empty;
    }
}