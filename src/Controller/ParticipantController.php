<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ParticipantController extends AbstractController
{
    #[Route('user/{id}', name: 'user_modifier')]
    public function modifierProfil(Request $req, ParticipantRepository $pr, $id, EntityManagerInterface $em, Participant $user): Response
    {
        $user = $pr->find($id);
        $userForm = $this -> createForm(ParticipantType::class, $user);
        $userForm->handleRequest($req);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $em->flush();
//            return $this->redirectToRoute('sorties');
        }

        return $this->render('user/modifier.html.twig', [
            'user' => $user,
            'userForm'=>$userForm->createView(),]
            );
    }
}