<?php

namespace App\Command;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:reset-password',
    description: 'Réinitialise le mot de passe d\'un utilisateur',
)]
class ResetPasswordCommand extends Command
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Nouveau mot de passe');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');

        $utilisateur = $this->utilisateurRepository->findOneBy(['email' => $email]);

        if (!$utilisateur) {
            $io->error(sprintf('Aucun utilisateur trouvé avec l\'email: %s', $email));
            return Command::FAILURE;
        }

        $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $plainPassword);
        $utilisateur->setPassword($hashedPassword);

        $this->entityManager->flush();

        $io->success(sprintf(
            'Mot de passe réinitialisé pour %s (%s %s)',
            $email,
            $utilisateur->getPrenom(),
            $utilisateur->getNom()
        ));

        return Command::SUCCESS;
    }
}
