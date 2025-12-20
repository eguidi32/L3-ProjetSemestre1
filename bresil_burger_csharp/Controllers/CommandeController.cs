using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using bresil_burger_csharp.Data;
using bresil_burger_csharp.Models;
using bresil_burger_csharp.Models.ViewModels;
using bresil_burger_csharp.Helpers;
using bresil_burger_csharp.Services;

namespace bresil_burger_csharp.Controllers
{
    public class CommandeController : Controller
    {
        private readonly ApplicationDbContext _context;
        private readonly CatalogueService _catalogueService;

        public CommandeController(ApplicationDbContext context, CatalogueService catalogueService)
        {
            _context = context;
            _catalogueService = catalogueService;
        }

        // GET: /Commande/Panier
        public IActionResult Panier()
        {
            var panier = GetPanier();
            var viewModel = new PanierViewModel { Items = panier };
            return View(viewModel);
        }

        // POST: /Commande/AjouterAuPanier
        [HttpPost]
        public async Task<IActionResult> AjouterAuPanier(string type, int id, int quantite = 1, List<int>? complementIds = null)
        {
            var panier = GetPanier();
            PanierItem? item = null;

            if (type == "burger")
            {
                var burger = await _catalogueService.GetBurgerByIdAsync(id);
                if (burger != null)
                {
                    decimal prixTotal = burger.Prix;
                    
                    if (complementIds != null && complementIds.Any())
                    {
                        var complements = await _context.Complements
                            .Where(c => complementIds.Contains(c.Id))
                            .ToListAsync();
                        prixTotal += complements.Sum(c => c.Prix);
                    }

                    item = new PanierItem
                    {
                        Id = burger.Id,
                        Type = "burger",
                        Nom = burger.Nom,
                        Prix = prixTotal,
                        Quantite = quantite,
                        Image = burger.Image,
                        ComplementIds = complementIds ?? new List<int>()
                    };
                }
            }
            else if (type == "menu")
            {
                var menu = await _catalogueService.GetMenuByIdAsync(id);
                if (menu != null)
                {
                    item = new PanierItem
                    {
                        Id = menu.Id,
                        Type = "menu",
                        Nom = menu.Nom,
                        Prix = menu.Prix,
                        Quantite = quantite,
                        Image = menu.Image
                    };
                }
            }

            if (item != null)
            {
                panier.Add(item);
                SavePanier(panier);
            }

            return RedirectToAction("Panier");
        }

        // POST: /Commande/SupprimerDuPanier
        [HttpPost]
        public IActionResult SupprimerDuPanier(int index)
        {
            var panier = GetPanier();
            
            if (index >= 0 && index < panier.Count)
            {
                panier.RemoveAt(index);
                SavePanier(panier);
            }

            return RedirectToAction("Panier");
        }

        // POST: /Commande/ViderPanier
        [HttpPost]
        public IActionResult ViderPanier()
        {
            SavePanier(new List<PanierItem>());
            return RedirectToAction("Panier");
        }

        // GET: /Commande/Valider
        public async Task<IActionResult> Valider()
        {
            var clientId = HttpContext.Session.GetInt32("ClientId");
            if (clientId == null)
            {
                return RedirectToAction("Login", "Auth");
            }

            var panier = GetPanier();
            if (!panier.Any())
            {
                return RedirectToAction("Panier");
            }

            var zones = await _context.Zones.ToListAsync();
            
            // Récupérer l'adresse du client
            var client = await _context.Clients.FindAsync(clientId);
            var adresseClient = client?.Adresse;

            var viewModel = new ValidationCommandeViewModel
            {
                Panier = new PanierViewModel { Items = panier },
                Zones = zones,
                AdresseEnregistree = adresseClient,
                UtiliserAdresseEnregistree = !string.IsNullOrEmpty(adresseClient)
            };

            return View(viewModel);
        }

        // POST: /Commande/Confirmer
        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> Confirmer(ValidationCommandeViewModel model)
        {
            var clientId = HttpContext.Session.GetInt32("ClientId");
            if (clientId == null)
            {
                return RedirectToAction("Login", "Auth");
            }

            var panier = GetPanier();
            if (panier == null || !panier.Any())
            {
                TempData["Error"] = "Votre panier est vide";
                return RedirectToAction("Panier");
            }

            // Préparer les données avec les prix depuis la BD
            var lignesData = new List<(int Id, string Type, int Quantite, decimal PrixUnitaire, List<int> ComplementIds)>();
            decimal montantTotal = 0;

            foreach (var item in panier)
            {
                decimal prixUnitaire = 0;
                
                if (item.Type == "burger")
                {
                    var burger = await _context.Burgers.FindAsync(item.Id);
                    if (burger != null)
                    {
                        prixUnitaire = burger.Prix;
                        if (item.ComplementIds != null && item.ComplementIds.Any())
                        {
                            var complements = await _context.Complements
                                .Where(c => item.ComplementIds.Contains(c.Id))
                                .ToListAsync();
                            prixUnitaire += complements.Sum(c => c.Prix);
                        }
                        Console.WriteLine($"DEBUG - Burger {item.Id}: {burger.Nom} - Prix: {burger.Prix}");
                    }
                    else
                    {
                        Console.WriteLine($"DEBUG - Burger {item.Id} NOT FOUND in database!");
                    }
                }
                else if (item.Type == "menu")
                {
                    var menu = await _context.Menus.FindAsync(item.Id);
                    if (menu != null)
                    {
                        prixUnitaire = menu.Prix;
                    }
                }

                if (prixUnitaire > 0)
                {
                    lignesData.Add((item.Id, item.Type, item.Quantite, prixUnitaire, item.ComplementIds ?? new List<int>()));
                    montantTotal += prixUnitaire * item.Quantite;
                }
            }

            if (montantTotal <= 0 || !lignesData.Any())
            {
                TempData["Error"] = "Impossible de calculer le montant de la commande";
                return RedirectToAction("Panier");
            }

            // Frais de livraison
            decimal fraisLivraison = 0;
            if (model.TypeLivraison == "LIVRAISON" && model.ZoneId.HasValue)
            {
                var zone = await _context.Zones.FindAsync(model.ZoneId.Value);
                fraisLivraison = zone?.PrixLivraison ?? 0;
            }

            // Calculer le montant total final
            var montantTotalFinal = montantTotal + fraisLivraison;
            
            // Vérifier que le montant est valide
            if (montantTotalFinal <= 0)
            {
                TempData["Error"] = "Impossible de calculer le montant de votre commande. Veuillez vérifier votre panier.";
                return RedirectToAction("Panier");
            }

            // Créer la commande avec initialisation d'objet
            var commande = new Commande
            {
                ClientId = clientId.Value,
                Numero = $"CMD-{DateTime.UtcNow:yyyyMMddHHmmss}-{clientId}",
                DateCommande = DateTime.UtcNow,
                Etat = "EN_ATTENTE",
                TypeLivraison = string.IsNullOrEmpty(model.TypeLivraison) ? "SUR_PLACE" : model.TypeLivraison,
                ZoneId = model.TypeLivraison == "LIVRAISON" ? model.ZoneId : null,
                MontantTotal = montantTotalFinal
            };
            
            _context.Commandes.Add(commande);
            await _context.SaveChangesAsync();

            // Créer les lignes de commande
            foreach (var ligne in lignesData)
            {
                var ligneCommande = new LigneCommande
                {
                    CommandeId = commande.Id,
                    BurgerId = ligne.Type == "burger" ? ligne.Id : null,
                    MenuId = ligne.Type == "menu" ? ligne.Id : null,
                    Quantite = ligne.Quantite,
                    PrixUnitaire = ligne.PrixUnitaire,
                    SousTotal = ligne.PrixUnitaire * ligne.Quantite,
                    TypeProduit = ligne.Type == "burger" ? "BURGER" : "MENU"
                };

                _context.LignesCommande.Add(ligneCommande);
                await _context.SaveChangesAsync();

                if (ligne.Type == "burger" && ligne.ComplementIds.Any())
                {
                    foreach (var compId in ligne.ComplementIds)
                    {
                        var ligneComplement = new LigneCommandeComplement
                        {
                            LigneCommandeId = ligneCommande.Id,
                            ComplementId = compId,
                        };
                        _context.LignesCommandeComplement.Add(ligneComplement);
                    }
                    await _context.SaveChangesAsync();
                }
            }

            SavePanier(new List<PanierItem>());

            return RedirectToAction("Paiement", new { id = commande.Id });
        }

        // GET: /Commande/Paiement/5
        public async Task<IActionResult> Paiement(int id)
        {
            var clientId = HttpContext.Session.GetInt32("ClientId");
            if (clientId == null)
            {
                return RedirectToAction("Login", "Auth");
            }

            var commande = await _context.Commandes
                .Include(c => c.Zone)
                .FirstOrDefaultAsync(c => c.Id == id && c.ClientId == clientId);

            if (commande == null)
            {
                return NotFound();
            }

            return View(commande);
        }

        // POST: /Commande/EffectuerPaiement
        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> EffectuerPaiement(int commandeId, string modePaiement, string telephone)
        {
            var clientId = HttpContext.Session.GetInt32("ClientId");
            if (clientId == null)
            {
                return RedirectToAction("Login", "Auth");
            }

            var commande = await _context.Commandes
                .FirstOrDefaultAsync(c => c.Id == commandeId && c.ClientId == clientId);

            if (commande == null)
            {
                return NotFound();
            }

            var paiementExistant = await _context.Paiements
                .FirstOrDefaultAsync(p => p.CommandeId == commandeId);

            if (paiementExistant != null)
            {
                return RedirectToAction("Confirmation", new { id = commandeId });
            }

            var paiement = new Paiement
            {
                CommandeId = commandeId,
                Montant = commande.MontantTotal,
                ModePaiement = modePaiement,
                Reference = $"{modePaiement.ToUpper()}-{DateTime.UtcNow:yyyyMMddHHmmss}-{commandeId}",
                DatePaiement = DateTime.UtcNow
            };

            _context.Paiements.Add(paiement);
            commande.Etat = "EN_PREPARATION";
            await _context.SaveChangesAsync();

            return RedirectToAction("Confirmation", new { id = commandeId });
        }

        // GET: /Commande/Confirmation/5
        public async Task<IActionResult> Confirmation(int id)
        {
            var clientId = HttpContext.Session.GetInt32("ClientId");
            if (clientId == null)
            {
                return RedirectToAction("Login", "Auth");
            }

            var commande = await _context.Commandes
                .Include(c => c.Paiement)
                .Include(c => c.Zone)
                .Include(c => c.LignesCommande)
                    .ThenInclude(lc => lc.Burger)
                .Include(c => c.LignesCommande)
                    .ThenInclude(lc => lc.Menu)
                .FirstOrDefaultAsync(c => c.Id == id && c.ClientId == clientId);

            if (commande == null)
            {
                return NotFound();
            }

            return View(commande);
        }

                // GET: /Commande/MesCommandes
        public async Task<IActionResult> MesCommandes()
        {
            var clientId = HttpContext.Session.GetInt32("ClientId");
            if (clientId == null)
            {
                return RedirectToAction("Login", "Auth");
            }

            var commandes = await _context.Commandes
                .Include(c => c.Paiement)
                .Include(c => c.Zone)
                .Where(c => c.ClientId == clientId)
                .OrderByDescending(c => c.DateCommande)
                .ToListAsync();

            return View(commandes);
        }

        // GET: /Commande/Details/5
        public async Task<IActionResult> Details(int id)
        {
            var clientId = HttpContext.Session.GetInt32("ClientId");
            if (clientId == null)
            {
                return RedirectToAction("Login", "Auth");
            }

            var commande = await _context.Commandes
                .Include(c => c.Paiement)
                .Include(c => c.Zone)
                .Include(c => c.LignesCommande)
                    .ThenInclude(lc => lc.Burger)
                .Include(c => c.LignesCommande)
                    .ThenInclude(lc => lc.Menu)
                        .ThenInclude(m => m!.Burger)
                .Include(c => c.LignesCommande)
                    .ThenInclude(lc => lc.Menu)
                        .ThenInclude(m => m!.Boisson)
                .Include(c => c.LignesCommande)
                    .ThenInclude(lc => lc.Menu)
                        .ThenInclude(m => m!.Frite)
                .FirstOrDefaultAsync(c => c.Id == id && c.ClientId == clientId);

            if (commande == null)
            {
                return NotFound();
            }

            return View(commande);
        }

        // POST: /Commande/Annuler/5
        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> Annuler(int id)
        {
            var clientId = HttpContext.Session.GetInt32("ClientId");
            if (clientId == null)
            {
                return RedirectToAction("Login", "Auth");
            }

            var commande = await _context.Commandes
                .Include(c => c.Paiement)
                .FirstOrDefaultAsync(c => c.Id == id && c.ClientId == clientId);

            if (commande == null)
            {
                TempData["Error"] = "Commande non trouvée";
                return RedirectToAction("MesCommandes");
            }

            // Vérifier que la commande n'est pas déjà payée
            if (commande.Paiement != null)
            {
                TempData["Error"] = "Impossible d'annuler une commande déjà payée";
                return RedirectToAction("MesCommandes");
            }

            // Vérifier que la commande n'est pas déjà annulée
            if (commande.Etat == "ANNULEE")
            {
                TempData["Error"] = "Cette commande est déjà annulée";
                return RedirectToAction("MesCommandes");
            }

            // Annuler la commande
            commande.Etat = "ANNULEE";
            await _context.SaveChangesAsync();

            TempData["Success"] = $"Commande #{commande.Id} annulée avec succès";
            return RedirectToAction("MesCommandes");
        }


        // Méthodes helper pour le panier en session
        private List<PanierItem> GetPanier()
        {
            return HttpContext.Session.GetObjectFromJson<List<PanierItem>>("Panier") ?? new List<PanierItem>();
        }

        private void SavePanier(List<PanierItem> panier)
        {
            HttpContext.Session.SetObjectAsJson("Panier", panier);
        }
    }
}