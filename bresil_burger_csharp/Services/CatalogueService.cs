using Microsoft.EntityFrameworkCore;
using bresil_burger_csharp.Data;
using bresil_burger_csharp.Models;

namespace bresil_burger_csharp.Services
{
    public class CatalogueService
    {
        private readonly ApplicationDbContext _context;

        public CatalogueService(ApplicationDbContext context)
        {
            _context = context;
        }

        // Récupérer tous les burgers non archivés
        public async Task<List<Burger>> GetBurgersAsync()
        {
            return await _context.Burgers
                .Where(b => !b.Archive)
                .OrderBy(b => b.Nom)
                .ToListAsync();
        }

        // Récupérer un burger par son ID
        public async Task<Burger?> GetBurgerByIdAsync(int id)
        {
            return await _context.Burgers
                .FirstOrDefaultAsync(b => b.Id == id && !b.Archive);
        }

        // Récupérer tous les menus non archivés avec leurs composants
        public async Task<List<Menu>> GetMenusAsync()
        {
            return await _context.Menus
                .Include(m => m.Burger)
                .Include(m => m.Boisson)
                .Include(m => m.Frite)
                .Where(m => !m.Archive)
                .OrderBy(m => m.Nom)
                .ToListAsync();
        }

        // Récupérer un menu par son ID avec ses composants
        public async Task<Menu?> GetMenuByIdAsync(int id)
        {
            return await _context.Menus
                .Include(m => m.Burger)
                .Include(m => m.Boisson)
                .Include(m => m.Frite)
                .FirstOrDefaultAsync(m => m.Id == id && !m.Archive);
        }

        // Récupérer tous les compléments non archivés
        public async Task<List<Complement>> GetComplementsAsync()
        {
            return await _context.Complements
                .Where(c => !c.Archive)
                .OrderBy(c => c.Type)
                .ThenBy(c => c.Nom)
                .ToListAsync();
        }

        // Récupérer les compléments par type (boisson ou frite)
        public async Task<List<Complement>> GetComplementsByTypeAsync(string type)
        {
            return await _context.Complements
                .Where(c => !c.Archive && c.Type == type)
                .OrderBy(c => c.Nom)
                .ToListAsync();
        }
    }
}