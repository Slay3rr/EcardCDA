<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search_type', ChoiceType::class, [
                'choices' => [
                    'Rechercher par titre/contenu' => 'article',
                    'Rechercher par catégorie' => 'category'
                ],
                'required' => true,
                'label' => false,
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('query', TextType::class, [
                'required' => true,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Rechercher...',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez entrer un terme de recherche'
                    ]),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 100,
                        'minMessage' => 'La recherche doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La recherche ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z0-9\s\-_àáâãäçèéêëìíîïñòóôõöùúûüýÿ]+$/',
                        'message' => 'La recherche contient des caractères non autorisés'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false, // Désactivé car c'est un formulaire GET
        ]);
    }
}