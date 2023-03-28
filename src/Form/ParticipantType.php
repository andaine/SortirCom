<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo')
            ->add('prenom')
            ->add('nom')
            ->add('telephone')
            ->add('email')
//            ->add('roles')
            ->add('password')
            ->add('site',EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'query_builder' => function (EntityRepository $er) {
                    return $er -> createQueryBuilder('s') -> orderBy('s.nom', 'ASC'); //fonction anonyme qui tri les séries par ordre alphabétiques
                }
            ])
//            ->add('administrateur')
//            ->add('actif')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
