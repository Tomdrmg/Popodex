<?php

namespace App\Form;

use App\Entity\Card;
use App\Entity\CardBack;
use App\Entity\Series;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class CardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la carte',
                'required' => true,
            ])
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('series', EntityType::class, [
                'label' => 'Série',
                'class' => Series::class,
                'choice_label' => 'title',
                'required' => true,
                'placeholder' => 'Choisir une série',
            ])
            ->add('back', EntityType::class, [
                'label' => 'Dos de carte',
                'class' => CardBack::class,
                'choice_label' => 'title',
                'required' => true,
                'placeholder' => 'Choisir un dos de carte',
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image',
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
            ->add('fullArt', CheckboxType::class, [
                'label' => 'Full Art',
                'required' => false,
            ])
            ->add('imageVerticalPosition', RangeType::class, [
                'label' => 'Position verticale de l\'image',
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'max' => 100,
                    'data-unit' => '%'
                ],
            ])
            ->add('borderOpacity', RangeType::class, [
                'label' => 'Opacité de la bordure',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                    'data-unit' => '%'
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
            ])
            ->add('movesMarginTop', RangeType::class, [
                'label' => 'Marge supérieure des attaques',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 200,
                    'data-unit' => 'px'
                ],
            ])
            ->add('moves', CollectionType::class, [
                'label' => false,
                'entry_type' => CardMoveType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'attr' => [
                    'class' => 'moves-collection',
                ],
            ])
            ->add('previewImage', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Card::class,
        ]);
    }
}
