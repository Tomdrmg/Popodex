<?php

namespace App\Form;

use App\Entity\CardBack;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Range;

class CardBackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
            ])
            ->add('backgroundImageFile', FileType::class, [
                'label' => 'Image de fond',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp'
                ],
                'constraints' => [
                    new Image([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG ou WebP)',
                    ])
                ],
            ])
            ->add('previewImage', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('borderOpacity', RangeType::class, [
                'label' => 'Opacité de la bordure',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                    'data-unit' => '%'
                ],
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => 100,
                    ])
                ],
            ])
            ->add('borderWidth', RangeType::class, [
                'label' => 'Largeur de la bordure',
                'required' => true,
                'attr' => [
                    'min' => 2,
                    'max' => 28,
                    'data-unit' => 'px'
                ],
                'constraints' => [
                    new Range([
                        'min' => 2,
                        'max' => 28,
                    ])
                ],
            ])
            ->add('fontSize', RangeType::class, [
                'label' => 'Taille de police',
                'required' => true,
                'attr' => [
                    'min' => 18,
                    'max' => 120,
                    'data-unit' => 'px'
                ],
                'constraints' => [
                    new Range([
                        'min' => 18,
                        'max' => 120,
                    ])
                ],
            ])
            ->add('outlineWidth', RangeType::class, [
                'label' => 'Largeur du contour',
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'max' => 30,
                    'data-unit' => 'px'
                ],
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'max' => 30,
                    ])
                ],
            ])
            ->add('textPosition', RangeType::class, [
                'label' => 'Position du texte',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 300,
                    'data-unit' => 'px'
                ],
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => 300,
                    ])
                ],
            ])
            ->add('curvature', RangeType::class, [
                'label' => 'Courbure',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                    'data-unit' => '%'
                ],
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => 100,
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CardBack::class,
        ]);
    }
}
