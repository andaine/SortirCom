<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateHeureDebut', DateTimeType::class,[
                    'widget'=>'single_text',
                    'view_timezone' => 'Europe/Paris',]
            )
            ->add('duree', IntegerType::class, [
                    'label'       => 'Durée (en min) ',
                    'required'    => true,
                    'attr'        => [
                        'min'      => 15,
                        'class'    => 'form-control',
                        'autocomplete' => 'off',
                    ]
                ]
            )
            ->add('dateLimiteInscription', DateType::class,[
                'widget'=>'single_text',
                'html5'  => true,


            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label'       => 'Nombre de participants max. ',
                'required'    => true,
                'attr'        => [
                    'min'      => 1,
                    'class'    => 'form-control',
                    'autocomplete' => 'off',
                ]
            ])
            ->add('infoSortie',TextareaType::class, [
                'attr' => [
                    'cols'=>30,
                ]
            ])
            ->add('lieu', EntityType::class,[
                    'class'=>Lieu::class,
                    'choice_label'=>'nom',

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
