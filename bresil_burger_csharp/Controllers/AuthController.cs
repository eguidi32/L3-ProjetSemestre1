using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using bresil_burger_csharp.Data;
using bresil_burger_csharp.Models;
using bresil_burger_csharp.Models.ViewModels;
using bresil_burger_csharp.Helpers;

namespace bresil_burger_csharp.Controllers
{
    public class AuthController : Controller
    {
        private readonly ApplicationDbContext _context;

        public AuthController(ApplicationDbContext context)
        {
            _context = context;
        }

        // GET: /Auth/Login
        public IActionResult Login()
        {
            // Si déjà connecté, rediriger vers le catalogue
            if (HttpContext.Session.GetInt32("ClientId") != null)
            {
                return RedirectToAction("Index", "Catalogue");
            }
            return View();
        }

        // POST: /Auth/Login
        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> Login(LoginViewModel model)
        {
            if (!ModelState.IsValid)
            {
                return View(model);
            }

            // Rechercher le client par email
            var client = await _context.Clients
                .FirstOrDefaultAsync(c => c.Email == model.Email);

            if (client == null)
            {
                ModelState.AddModelError("", "Email ou mot de passe incorrect");
                return View(model);
            }

            // Vérifier le mot de passe
            if (!BCrypt.Net.BCrypt.Verify(model.MotDePasse, client.MotDePasse))
            {
                ModelState.AddModelError("", "Email ou mot de passe incorrect");
                return View(model);
            }

            // Connexion réussie - Stocker les infos en session
            HttpContext.Session.SetInt32("ClientId", client.Id);
            HttpContext.Session.SetString("ClientNom", client.Nom);
            HttpContext.Session.SetString("ClientPrenom", client.Prenom);
            HttpContext.Session.SetString("ClientEmail", client.Email);

            return RedirectToAction("Index", "Catalogue");
        }

        // GET: /Auth/Register
        public IActionResult Register()
        {
            // Si déjà connecté, rediriger vers le catalogue
            if (HttpContext.Session.GetInt32("ClientId") != null)
            {
                return RedirectToAction("Index", "Catalogue");
            }
            return View();
        }

        // POST: /Auth/Register
        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> Register(RegisterViewModel model)
        {
            if (!ModelState.IsValid)
            {
                return View(model);
            }

            // Vérifier si l'email existe déjà
            var existingClient = await _context.Clients
                .FirstOrDefaultAsync(c => c.Email == model.Email);

            if (existingClient != null)
            {
                ModelState.AddModelError("Email", "Cet email est déjà utilisé");
                return View(model);
            }

            // Créer le nouveau client
            var client = new Client
            {
                Nom = model.Nom,
                Prenom = model.Prenom,
                Telephone = model.Telephone,
                Email = model.Email,
                Adresse = model.Adresse,
                MotDePasse = BCrypt.Net.BCrypt.HashPassword(model.MotDePasse),
                DateInscription = DateTime.Now
            };

            _context.Clients.Add(client);
            await _context.SaveChangesAsync();

            // Connexion automatique après inscription
            HttpContext.Session.SetInt32("ClientId", client.Id);
            HttpContext.Session.SetString("ClientNom", client.Nom);
            HttpContext.Session.SetString("ClientPrenom", client.Prenom);
            HttpContext.Session.SetString("ClientEmail", client.Email);

            return RedirectToAction("Index", "Catalogue");
        }

        // GET: /Auth/Logout
        public IActionResult Logout()
        {
            HttpContext.Session.Clear();
            return RedirectToAction("Index", "Home");
        }
    }
}