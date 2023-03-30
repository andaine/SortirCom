<?php

namespace App\Form;

use App\Class\filtre;
use App\Entity\Site;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'query_builder' => function (SiteRepository $sr) {
                    return $sr -> createQueryBuilder('s') -> orderBy('s.nom', 'ASC'); //fonction anonyme qui tri les sites par ordre alphabétique
                },
                'required' => false
            ])
            ->add('global', TextType::class, [
                'label' => 'Le nom de la sortie contient',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher'
    ]
            ])
            ->add('dateDebut', DateTimeType::class,[
                'label' => 'Entre',
                'html5' => true,
                'widget' => 'single_text',
                'required' => false

            ])
            ->add('dateFin', DateTimeType::class,[
                'label' => 'et',
                'html5' => true,
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('organisateur',CheckboxType::class, [
                'label'    => 'Sorties dont je suis l\'organisateur',
                'required' => false

            ])
            ->add('inscrit',CheckboxType::class, [
                'label'    => 'Sorties auxquelles je suis inscrit/e',
                'required' => false
            ])
            ->add('pasInscrit',CheckboxType::class, [
                'label'    => 'Sorties auxquelles je ne suis pas inscrit/e',
                'required' => false
            ])
            ->add('sortiePassee',CheckboxType::class, [
                'label'    => 'Sorties passées',
                'required' => false
            ])
            ->add('rechercher', SubmitType::class, [
                'label' => 'Rechercher'

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => filtre::class,
        ]);
    }
}
