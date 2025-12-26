<?php

namespace App\Controller\Gestionnaire;

use App\Entity\Complement;
use App\Form\ComplementType;
use App\Repository\ComplementRepository;
use App\Service\CloudinaryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/gestionnaire/complements')]
#[IsGranted('ROLE_GESTIONNAIRE')]
class ComplementController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CloudinaryService $cloudinaryService
    ) {}

    #[Route('', name: 'app_gestionnaire_complements')]
    public function index(ComplementRepository $complementRepository): Response
    {
        $complements = $complementRepository->findAll();
        
        // Séparer boissons et frites pour l'affichage
        $boissons = array_filter($complements, fn($c) => $c->getTypeComplement() === 'BOISSON');
        $frites = array_filter($complements, fn($c) => $c->getTypeComplement() === 'FRITES');

        return $this->render('gestionnaire/complement/index.html.twig', [
            'complements' => $complements,
            'boissons' => $boissons,
            'frites' => $frites,
        ]);
    }

    #[Route('/nouveau', name: 'app_gestionnaire_complement_new')]
    public function new(Request $request): Response
    {
        $complement = new Complement();
        $form = $this->createForm(ComplementType::class, $complement, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Upload de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $folder = $complement->getTypeComplement() === 'BOISSON' ? 'brasil_burger/boissons' : 'brasil_burger/frites';
                $imageUrl = $this->cloudinaryService->upload($imageFile, $folder);
                if ($imageUrl) {
                    $complement->setImage($imageUrl);
                }
            }

            $complement->setDateCreation(new \DateTime());
            $complement->setArchive(false);

            $this->entityManager->persist($complement);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le complément "' . $complement->getNom() . '" a été créé avec succès !');
            return $this->redirectToRoute('app_gestionnaire_complements');
        }

        return $this->render('gestionnaire/complement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_gestionnaire_complement_edit')]
    public function edit(Request $request, Complement $complement): Response
    {
        $form = $this->createForm(ComplementType::class, $complement, ['is_new' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $folder = $complement->getTypeComplement() === 'BOISSON' ? 'brasil_burger/boissons' : 'brasil_burger/frites';
                $imageUrl = $this->cloudinaryService->upload($imageFile, $folder);
                if ($imageUrl) {
                    $complement->setImage($imageUrl);
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Le complément "' . $complement->getNom() . '" a été modifié avec succès !');
            return $this->redirectToRoute('app_gestionnaire_complements');
        }

        return $this->render('gestionnaire/complement/edit.html.twig', [
            'form' => $form->createView(),
            'complement' => $complement,
        ]);
    }

    #[Route('/{id}/archiver', name: 'app_gestionnaire_complement_archive', methods: ['POST'])]
    public function archive(Request $request, Complement $complement): Response
    {
        if ($this->isCsrfTokenValid('archive' . $complement->getId(), $request->request->get('_token'))) {
            $complement->setArchive(!$complement->isArchive());
            $this->entityManager->flush();

            $action = $complement->isArchive() ? 'archivé' : 'restauré';
            $this->addFlash('success', 'Le complément "' . $complement->getNom() . '" a été ' . $action . ' !');
        }

        return $this->redirectToRoute('app_gestionnaire_complements');
    }

    #[Route('/{id}', name: 'app_gestionnaire_complement_show')]
    public function show(Complement $complement): Response
    {
        return $this->render('gestionnaire/complement/show.html.twig', [
            'complement' => $complement,
        ]);
    }
}