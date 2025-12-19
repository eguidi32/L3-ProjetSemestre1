using Microsoft.AspNetCore.Mvc;
using bresil_burger_csharp.Services;
using bresil_burger_csharp.Models.ViewModels;

namespace bresil_burger_csharp.Controllers
{
    public class CatalogueController : Controller
    {
        private readonly CatalogueService _catalogueService;

        public CatalogueController(CatalogueService catalogueService)
        {
            _catalogueService = catalogueService;
        }

        // GET: /Catalogue ou /Catalogue?filtre=burger ou /Catalogue?filtre=menu
        public async Task<IActionResult> Index(string filtre = "tous")
        {
            var viewModel = new CatalogueViewModel
            {
                Filtre = filtre
            };

            // Charger les données selon le filtre
            if (filtre == "tous" || filtre == "burger")
            {
                viewModel.Burgers = await _catalogueService.GetBurgersAsync();
            }

            if (filtre == "tous" || filtre == "menu")
            {
                viewModel.Menus = await _catalogueService.GetMenusAsync();
            }

            return View(viewModel);
        }

        // GET: /Catalogue/Burger/5
        public async Task<IActionResult> Burger(int id)
        {
            var burger = await _catalogueService.GetBurgerByIdAsync(id);

            if (burger == null)
            {
                return NotFound();
            }

            // Récupérer les compléments disponibles
            ViewBag.Complements = await _catalogueService.GetComplementsAsync();

            return View(burger);
        }

        // GET: /Catalogue/Menu/5
        public async Task<IActionResult> Menu(int id)
        {
            var menu = await _catalogueService.GetMenuByIdAsync(id);

            if (menu == null)
            {
                return NotFound();
            }

            return View(menu);
        }
    }
}