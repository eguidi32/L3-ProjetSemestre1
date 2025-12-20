using Microsoft.EntityFrameworkCore;
using bresil_burger_csharp.Models;

namespace bresil_burger_csharp.Data
{
    public class ApplicationDbContext : DbContext
    {
        public ApplicationDbContext(DbContextOptions<ApplicationDbContext> options) 
            : base(options)
        {
        }

        public DbSet<Burger> Burgers { get; set; }
        public DbSet<Complement> Complements { get; set; }
        public DbSet<Menu> Menus { get; set; }
        public DbSet<Client> Clients { get; set; }
        public DbSet<Zone> Zones { get; set; }
        public DbSet<Commande> Commandes { get; set; }
        public DbSet<LigneCommande> LignesCommande { get; set; }
        public DbSet<LigneCommandeComplement> LignesCommandeComplement { get; set; }
        public DbSet<Paiement> Paiements { get; set; }

        protected override void OnModelCreating(ModelBuilder modelBuilder)
        {
            base.OnModelCreating(modelBuilder);

            // Configuration des noms de tables (pour correspondre à la BD existante)
            modelBuilder.Entity<Burger>().ToTable("burger");
            modelBuilder.Entity<Complement>().ToTable("complement");
            modelBuilder.Entity<Menu>().ToTable("menu");
            modelBuilder.Entity<Client>().ToTable("utilisateur");
            modelBuilder.Entity<Zone>().ToTable("zone");
            modelBuilder.Entity<Commande>().ToTable("commande");
            modelBuilder.Entity<LigneCommande>().ToTable("ligne_commande");
            modelBuilder.Entity<LigneCommandeComplement>().ToTable("ligne_commande_complement");
            modelBuilder.Entity<Paiement>().ToTable("paiement");

            // Relations Menu
            modelBuilder.Entity<Menu>()
                .HasOne(m => m.Burger)
                .WithMany()
                .HasForeignKey(m => m.BurgerId);

            modelBuilder.Entity<Menu>()
                .HasOne(m => m.Boisson)
                .WithMany()
                .HasForeignKey(m => m.BoissonId);

            modelBuilder.Entity<Menu>()
                .HasOne(m => m.Frite)
                .WithMany()
                .HasForeignKey(m => m.FriteId);

            // Relation Commande -> Client
            modelBuilder.Entity<Commande>()
                .HasOne(c => c.Client)
                .WithMany()
                .HasForeignKey(c => c.ClientId);

            // Relation Commande -> Zone
            modelBuilder.Entity<Commande>()
                .HasOne(c => c.Zone)
                .WithMany()
                .HasForeignKey(c => c.ZoneId);

            // Configuration explicite du MontantTotal
            modelBuilder.Entity<Commande>()
                .Property(c => c.MontantTotal)
                .HasColumnName("montant_total")
                .HasColumnType("decimal(10,2)")
                .IsRequired();

            // Relation LigneCommande -> Commande
            modelBuilder.Entity<LigneCommande>()
                .HasOne(lc => lc.Commande)
                .WithMany(c => c.LignesCommande)
                .HasForeignKey(lc => lc.CommandeId);

            // Relation Paiement -> Commande (1 à 1)
            modelBuilder.Entity<Paiement>()
                .HasOne(p => p.Commande)
                .WithOne(c => c.Paiement)
                .HasForeignKey<Paiement>(p => p.CommandeId);
        }
    }
}