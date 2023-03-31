<?php

namespace App\Controller;

use App\Class\filtre;
use App\Entity\Etat;
use App\Entity\Inscription;
use App\Entity\Sortie;
use App\Form\FiltreType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\InscriptionRepository;
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
    public function afficherToutesSorties(SortieRepository $sortieRepository, Request $req)
    {
        //Créer une instance de filtre
        $filtre = new filtre();
        $date = new \DateTime();

        $userConnecte = $this->getUser();//->getId();

        $sortieForm = $this->createForm(FiltreType::class, $filtre);
        $sortieForm->handleRequest($req);

        $sorties = $sortieRepository->findByFiltre($filtre,$userConnecte);


        return $this->render('sortie/sorties.html.twig', [
            'controller_name' => 'SortieController',
            "sorties" => $sorties,
            'filtreForm' => $sortieForm->createView(),
            'date' => $date
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

            $this->addFlash("success", "Sortie ajoutée ! ");

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
                                   InscriptionRepository $inscriptionRepository,
                                   $idsortie,
                                   Request $request): Response
        {

            //je dois récuperer l'user connecter & la sortie selectionner
            $user = $this->getUser();
            $sortie = $sortieRepository->find($idsortie);

            //chercher en BDD si l'inscription existe sur l'user_id et la sortie_id
            $inscription = $inscriptionRepository->findBy(['participant'=>$user, 'sortie'=>$sortie]);


            if ($inscription ==  null){
                $inscription = new Inscription();
                $inscription->setParticipant($user);
                $inscription->setSortie($sortie);
                $inscription->setDate(new \DateTime('now'));
                $entityManager->persist($inscription);
                $entityManager->flush();
                $this->addFlash("success", "participation ajouter ! ");
            return $this->redirectToRoute('sorties');
            }

            $this->addFlash("warning", "Participant déja inscrit ! ");
            return $this->redirectToRoute('sorties');

        }

    #[Route('seDesister/{idsortie}', name: 'desister')]
    public function seDesister(EntityManagerInterface $entityManager,
                               ParticipantRepository $participantRepository,
                               SortieRepository $sortieRepository,
                               InscriptionRepository $inscriptionRepository,
                                                      $idsortie,
                               Request $request): Response
    {

        // récupérer l'utilisateur connecté et la sortie sélectionnée
        $user = $this->getUser();
        $sortie = $sortieRepository->find($idsortie);

        // récupérer l'inscription de l'utilisateur pour la sortie
        $inscription = $inscriptionRepository->findOneBy(['participant'=>$user, 'sortie'=>$sortie]);

        if ($inscription !== null) {
            $entityManager->remove($inscription);
            $entityManager->flush();
            $this->addFlash("success", "Désinscription réussie ! ");
        } else {
            $this->addFlash("warning", "Participant non inscrit à la sortie ! ");
        }

        return $this->redirectToRoute('sorties');
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
