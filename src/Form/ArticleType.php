<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;
use Doctrine\ORM\EntityManagerInterface;

class ArticleType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder
        ->add('Titre', TextType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Le titre ne peut pas être vide']),
                new Length([
                    'max' => 255,
                    'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères'
                ])
            ]
        ])
        ->add('content', TextareaType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Le contenu ne peut pas être vide']),
                new Length([
                    'max' => 255,
                    'maxMessage' => 'Le contenu ne peut pas dépasser {{ limit }} caractères'
                ])
            ]
        ])
        ->add('price', NumberType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Le prix ne peut pas être vide']),
                new Positive(['message' => 'Le prix doit être positif'])
            ]
        ])
        ->add('Category', EntityType::class, [
            'class' => Category::class,
            'multiple' => true,
            'expanded' => false,
            'choice_label' => 'name',
            'by_reference' => false,
            'invalid_message' => 'Catégorie invalide'
        ])
    ;
}
public function configureOptions(OptionsResolver $resolver)
{
    $resolver->setDefaults([
        'data_class' => Article::class,
        'csrf_protection' => false,  // Désactive la protection CSRF
        'allow_extra_fields' => true
    ]);
}
}