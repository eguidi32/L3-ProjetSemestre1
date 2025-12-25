<?php

namespace App\Controller\Gestionnaire;

use App\Entity\Menu;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use App\Service\CloudinaryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gestionnaire/menus')]
#[IsGranted('ROLE_GESTIONNAIRE')]
class MenuController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CloudinaryService $cloudinaryService
    ) {}

    #[Route('', name: 'app_gestionnaire_menus')]
    public function index(MenuRepository $menuRepository): Response
    {
        $menus = $menuRepository->findAll();

        return $this->render('gestionnaire/menu/index.html.twig', [
            'menus' => $menus,
        ]);
    }

    #[Route('/nouveau', name: 'app_gestionnaire_menu_new')]
    public function new(Request $request): Response
    {
        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Upload de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $imageUrl = $this->cloudinaryService->upload($imageFile, 'brasil_burger/menus');
                if ($imageUrl) {
                    $menu->setImage($imageUrl);
                }
            }

            $menu->setDateCreation(new \DateTime());
            $menu->setArchive(false);

            $this->entityManager->persist($menu);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le menu "' . $menu->getNom() . '" a été créé avec succès !');
            return $this->redirectToRoute('app_gestionnaire_menus');
        }

        return $this->render('gestionnaire/menu/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_gestionnaire_menu_edit')]
    public function edit(Request $request, Menu $menu): Response
    {
        $form = $this->createForm(MenuType::class, $menu, ['is_new' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $imageUrl = $this->cloudinaryService->upload($imageFile, 'brasil_burger/menus');
                if ($imageUrl) {
                    $menu->setImage($imageUrl);
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Le menu "' . $menu->getNom() . '" a été modifié avec succès !');
            return $this->redirectToRoute('app_gestionnaire_menus');
        }

        return $this->render('gestionnaire/menu/edit.html.twig', [
            'form' => $form->createView(),
            'menu' => $menu,
        ]);
    }

    #[Route('/{id}/archiver', name: 'app_gestionnaire_menu_archive', methods: ['POST'])]
    public function archive(Request $request, Menu $menu): Response
    {
        if ($this->isCsrfTokenValid('archive' . $menu->getId(), $request->request->get('_token'))) {
            $menu->setArchive(!$menu->isArchive());
            $this->entityManager->flush();

            $action = $menu->isArchive() ? 'archivé' : 'restauré';
            $this->addFlash('success', 'Le menu "' . $menu->getNom() . '" a été ' . $action . ' !');
        }

        return $this->redirectToRoute('app_gestionnaire_menus');
    }

    #[Route('/{id}', name: 'app_gestionnaire_menu_show')]
    public function show(Menu $menu): Response
    {
        return $this->render('gestionnaire/menu/show.html.twig', [
            'menu' => $menu,
        ]);
    }
}