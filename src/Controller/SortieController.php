<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/sorties', name: 'sorties')]
    public function afficherToutesSorties(SortieRepository $sortieRepository): Response
    {
        // todo choisir le type de tri pour l'affichage des sortie par défaut
        $sorties = $sortieRepository->findAll();

        return $this->render('sortie/sorties.html.twig', [
            'controller_name' => 'SortieController',
            "sorties"=>$sorties
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
    public function nouvelleSortie(Request $request, EntityManagerInterface $entityManager,EtatRepository $etatRepository): Response
    {
        $sortie = new Sortie();

        $sortie->setDateHeureDebut(new \DateTime('now'));

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        //ajout de la date du jour


        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){
           $etat = $etatRepository->find(1);
           $sortie->setEtat($etat);

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
    public function modifierSortie(int $id,
                                   SortieRepository $sortieRepository,
                                   Request $request,
                                   EntityManagerInterface $entityManager,
                                   EtatRepository $etatRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($request->get('publier') == 'publier'){
            $this->publierSortie($id,$sortieRepository,$request,$entityManager,$etatRepository);
        }

        if ($request->get('supprimer') == 'supprimer'){
            $this->supprimerSortie($id,$sortieRepository,$request,$entityManager,$etatRepository);
        }

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){

            $entityManager->flush();
            $this->addFlash("success", "Sortie Modifié ! ");
            return $this->redirectToRoute('sorties');
        }

        return $this->render('sortie/nouvellesortie.html.twig', [
            'sortieForm' => $sortieForm,
            'sortie'=>$sortie

        ]);
    }


    public function publierSortie(int $id,
                                  SortieRepository $sortieRepository,
                                  Request $request,
                                  EntityManagerInterface $entityManager,
                                  EtatRepository $etatRepository){
        $sortie = $sortieRepository->find($id);
        $etat = $etatRepository->find(2);
        $sortie->setEtat($etat);
        $entityManager->flush();
        $this->addFlash("success", "Sortie Publié ! ");
        return $this->redirectToRoute("sorties");
    }
    public function supprimerSortie(int $id,
                                   SortieRepository $sortieRepository,
                                   Request $request,
                                   EntityManagerInterface $entityManager,
                                    EtatRepository $etatRepository

                                  ): Response
    {
        $sortie = $sortieRepository->find($id);
        $etat = $etatRepository->find(6);
        $sortie->setEtat($etat);
        $entityManager->flush();
        $this->addFlash("success", "Sortie Annuler ! ");
    return $this->redirectToRoute('sorties');

    }






}
