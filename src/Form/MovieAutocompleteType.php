<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovieAutocompleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('query', TextType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => 'Rechercher un film...',
                'class' => 'form-control',
                'autocomplete' => 'off'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'attr' => [
                'data-controller' => 'movie-search',
                'data-movie-search-url-value' => '/api/movies/search',
                'data-movie-search-min-chars-value' => '2'
            ]
        ]);
    }
}
