<?php

namespace App\Form;

use App\Entity\Burger;
use App\Entity\Complement;
use App\Entity\Menu;
use App\Repository\BurgerRepository;
use App\Repository\ComplementRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du menu',
                'attr' => [
                    'placeholder' => 'Ex: Menu Brasil Deluxe',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire'])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description du menu...',
                    'class' => 'form-control',
                    'rows' => 3
                ]
            ])
            ->add('burger', EntityType::class, [
                'class' => Burger::class,
                'choice_label' => function (Burger $burger) {
                    return $burger->getNom() . ' - ' . number_format((float)$burger->getPrix(), 0, ',', ' ') . ' F';
                },
                'label' => 'Burger',
                'placeholder' => 'Sélectionner un burger',
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (BurgerRepository $repo) {
                    return $repo->createQueryBuilder('b')
                        ->where('b.archive = false')
                        ->orderBy('b.nom', 'ASC');
                },
                'constraints' => [
                    new NotBlank(['message' => 'Le burger est obligatoire'])
                ]
            ])
            ->add('boisson', EntityType::class, [
                'class' => Complement::class,
                'choice_label' => function (Complement $c) {
                    return $c->getNom() . ' - ' . number_format((float)$c->getPrix(), 0, ',', ' ') . ' F';
                },
                'label' => 'Boisson',
                'placeholder' => 'Sélectionner une boisson',
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (ComplementRepository $repo) {
                    return $repo->createQueryBuilder('c')
                        ->where('c.archive = false')
                        ->andWhere('c.typeComplement = :type')
                        ->setParameter('type', 'BOISSON')
                        ->orderBy('c.nom', 'ASC');
                },
                'constraints' => [
                    new NotBlank(['message' => 'La boisson est obligatoire'])
                ]
            ])
            ->add('frites', EntityType::class, [
                'class' => Complement::class,
                'choice_label' => function (Complement $c) {
                    return $c->getNom() . ' - ' . number_format((float)$c->getPrix(), 0, ',', ' ') . ' F';
                },
                'label' => 'Frites',
                'placeholder' => 'Sélectionner des frites',
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (ComplementRepository $repo) {
                    return $repo->createQueryBuilder('c')
                        ->where('c.archive = false')
                        ->andWhere('c.typeComplement = :type')
                        ->setParameter('type', 'FRITES')
                        ->orderBy('c.nom', 'ASC');
                },
                'constraints' => [
                    new NotBlank(['message' => 'Les frites sont obligatoires'])
                ]
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => $options['is_new'],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'constraints' => $options['is_new'] ? [
                    new NotBlank(['message' => 'L\'image est obligatoire']),
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide',
                    ])
                ] : [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide',
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
            'is_new' => false,
        ]);
    }
}