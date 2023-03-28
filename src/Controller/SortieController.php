<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
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
    public function nouvelleSortie(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){
           // $sortie->setEtat();
            $entityManager->persist($sortie);
            $entityManager->flush();

        }




        return $this->render('sortie/nouvellesortie.html.twig', [
            'sortieForm' => $sortieForm

        ]);
    }
}
