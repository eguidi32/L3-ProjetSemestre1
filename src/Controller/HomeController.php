<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Si l'utilisateur est connecté et a le rôle gestionnaire, rediriger vers le dashboard
        if ($this->isGranted('ROLE_GESTIONNAIRE')) {
            return $this->redirectToRoute('app_gestionnaire_dashboard');
        }
        
        // Sinon, rediriger vers la page de connexion
        return $this->redirectToRoute('app_login');
    }

    #[Route('/health', name: 'app_health')]
    public function health(): Response
    {
        return new Response('OK', Response::HTTP_OK);
    }
}
