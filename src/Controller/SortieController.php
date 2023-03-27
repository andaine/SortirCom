<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/sorties', name: 'sorties')]
    public function afficherToutesSorties(SortieRepository $sortieRepository): Response
    {
        // todo choisir le type de tri pour l'affichage des sortie par défaut
        $sorties = $sortieRepository->findAll();

        return $this->render('sorties.html.twig', [
            'controller_name' => 'SortieController',
            "sorties"=>$sorties
        ]);
    }
}
