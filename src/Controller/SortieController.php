<?php

namespace App\Controller;

use App\Class\filtre;
use App\Entity\Etat;
use App\Entity\Inscription;
use App\Entity\Sortie;
use App\Form\FiltreType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/sorties', name: 'sorties')]
    public function afficherToutesSorties(SortieRepository $sortieRepository, Request $req): Response
    {
        //Créer une instance de filtre
        $filtre = new filtre();

        $sortieForm = $this->createForm(FiltreType::class, $filtre);
        $sortieForm->handleRequest($req);

        $sorties = $sortieRepository->findByFiltre($filtre);

        //$sorties = $sortieRepository->findBy(['etat' => [1, 2, 3, 4]]);

        return $this->render('sortie/sorties.html.twig', [
            'controller_name' => 'SortieController',
            "sorties" => $sorties,
            'filtreForm' => $sortieForm
        ]);
    }


    #[Route('/sortie/{id}', name: 'sortie')]
    public function detailsSortie(int $id, SortieRepository $sortieRepository): Response
    {
        //récupération par l'id d'une sortie
        $sortie = $sortieRepository->find($id);


        return $this->render('sortie/sortiedetail.html.twig', [
            'sortie' => $sortie

        ]);
    }

    #[Route('/nouvellesortie', name: 'nouvelle_sortie')]
    public function nouvelleSortie(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $sortie = new Sortie();

        $sortie->setDateHeureDebut(new \DateTime('now'));

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        //ajout de la date du jour

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $etat = $etatRepository->find(1);
            $sortie->setEtat($etat);
            $user = $this->getUser();
            $sortie->setOrganisateur($user);
            $sortie->setSite($user->getSite());


            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash("success", "Sortie Ajouter ! ");
            return $this->redirectToRoute('sorties');
        }

        return $this->render('sortie/nouvellesortie.html.twig', [
            'sortieForm' => $sortieForm

        ]);
    }

    #[Route('/modifiersortie/{id}', name: 'modifier_sortie')]
    public function modifierSortie(int                    $id,
                                   SortieRepository       $sortieRepository,
                                   Request                $request,
                                   EntityManagerInterface $entityManager,
                                   EtatRepository         $etatRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);
        $modificationAutre = false;

        if ($request->get('publier') == 'publier') {
            return $this->publierSortie($id, $sortieRepository, $entityManager, $etatRepository);


        }

        if ($request->get('supprimer') == 'supprimer') {
           return $this->supprimerSortie($id, $sortieRepository, $entityManager, $etatRepository);
        }

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $entityManager->flush();
            $this->addFlash("success", "Sortie Modifié ! ");
            return $this->redirectToRoute('sorties');
        }

        return $this->render('sortie/nouvellesortie.html.twig', [
            'sortieForm' => $sortieForm,
            'sortie' => $sortie

        ]);
    }


    #[Route('participer/{idsortie}', name: 'participer')]   //id de l'evenement
    public function participer(EntityManagerInterface $entityManager,
                               ParticipantRepository $participantRepository,
                               SortieRepository $sortieRepository,
                               $idsortie,
                               int $idparticipant,
                               Request $request): Response
    {       //problème user recuperer id

        $user = $participantRepository->find($idparticipant);
        $sortie = $sortieRepository->find($idsortie);

        $inscription = new Inscription();
        $inscription->setParticipant($user->getId());
        $inscription->setSortie($sortie->getId());
        $inscription->setDate(new \DateTime('now'));

        $entityManager->persist($inscription);
        $entityManager->flush();
        $this->addFlash("success", "Participation Ajouter ! ");



        return $this->render('user/monProfil.html.twig', [
            'user'=>$user
        ]);
    }



    public function publierSortie(int                    $id,
                                  SortieRepository       $sortieRepository,
                                  EntityManagerInterface $entityManager,
                                  EtatRepository         $etatRepository)
    {
        $sortie = $sortieRepository->find($id);
        $etat = $etatRepository->find(2);
        $sortie->setEtat($etat);
        $entityManager->flush();
        $this->addFlash("success", "Sortie Publié ! ");
        return $this->redirectToRoute("sorties");
    }

    public function supprimerSortie(int                    $id,
                                    SortieRepository       $sortieRepository,
                                    EntityManagerInterface $entityManager,
                                    EtatRepository         $etatRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $etat = $etatRepository->find(6);
        $sortie->setEtat($etat);
        $entityManager->flush();
        $this->addFlash("success", "Sortie Annulée ! ");
        return $this->redirectToRoute("sorties");
    }



}
