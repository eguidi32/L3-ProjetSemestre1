<?php

namespace App\Controller\Gestionnaire;

use App\Entity\Burger;
use App\Form\BurgerType;
use App\Repository\BurgerRepository;
use App\Service\CloudinaryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gestionnaire/burgers')]
#[IsGranted('ROLE_GESTIONNAIRE')]
class BurgerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CloudinaryService $cloudinaryService
    ) {}

    #[Route('', name: 'app_gestionnaire_burgers')]
    public function index(BurgerRepository $burgerRepository): Response
    {
        $burgers = $burgerRepository->findAll();

        return $this->render('gestionnaire/burger/index.html.twig', [
            'burgers' => $burgers,
        ]);
    }

    #[Route('/nouveau', name: 'app_gestionnaire_burger_new')]
    public function new(Request $request): Response
    {
        $burger = new Burger();
        $form = $this->createForm(BurgerType::class, $burger, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Upload de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $imageUrl = $this->cloudinaryService->upload($imageFile, 'brasil_burger/burgers');
                if ($imageUrl) {
                    $burger->setImage($imageUrl);
                }
            }

            $burger->setDateCreation(new \DateTime());
            $burger->setArchive(false);

            $this->entityManager->persist($burger);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le burger "' . $burger->getNom() . '" a été créé avec succès !');
            return $this->redirectToRoute('app_gestionnaire_burgers');
        }

        return $this->render('gestionnaire/burger/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_gestionnaire_burger_edit')]
    public function edit(Request $request, Burger $burger): Response
    {
        $form = $this->createForm(BurgerType::class, $burger, ['is_new' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Upload de la nouvelle image si fournie
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $imageUrl = $this->cloudinaryService->upload($imageFile, 'brasil_burger/burgers');
                if ($imageUrl) {
                    $burger->setImage($imageUrl);
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Le burger "' . $burger->getNom() . '" a été modifié avec succès !');
            return $this->redirectToRoute('app_gestionnaire_burgers');
        }

        return $this->render('gestionnaire/burger/edit.html.twig', [
            'form' => $form->createView(),
            'burger' => $burger,
        ]);
    }

    #[Route('/{id}/archiver', name: 'app_gestionnaire_burger_archive', methods: ['POST'])]
    public function archive(Request $request, Burger $burger): Response
    {
        if ($this->isCsrfTokenValid('archive' . $burger->getId(), $request->request->get('_token'))) {
            $burger->setArchive(!$burger->isArchive());
            $this->entityManager->flush();

            $action = $burger->isArchive() ? 'archivé' : 'restauré';
            $this->addFlash('success', 'Le burger "' . $burger->getNom() . '" a été ' . $action . ' !');
        }

        return $this->redirectToRoute('app_gestionnaire_burgers');
    }

    #[Route('/{id}', name: 'app_gestionnaire_burger_show')]
    public function show(Burger $burger): Response
    {
        return $this->render('gestionnaire/burger/show.html.twig', [
            'burger' => $burger,
        ]);
    }
}