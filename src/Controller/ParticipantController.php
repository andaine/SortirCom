<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class ParticipantController extends AbstractController
{
    #[Route('user/modify/{id}', name: 'user_modifier')]
    public function modifierProfil(Request $req, ParticipantRepository $pr, $id, EntityManagerInterface $em): Response
    {
        $user = $pr->find($id);
        $user->setPassword('');
        $userForm = $this->createForm(ParticipantType::class, $user);
        $userForm->handleRequest($req);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            if($userForm->get('enregistrer')->isClicked()) {
                $em->flush();
                return $this->redirectToRoute('user_monProfil',['id'=> $user->getId()]);
            } else {
                return $this->redirectToRoute('user_monProfil',['id'=> $user->getId()]);
            }
        }

        return $this->render('user/modifier.html.twig', [
            'user' => $user,
            'userForm'=>$userForm->createView(),]
            );
    }

    #[Route('user/{id}', name: 'user_monProfil')]
    public function monProfil(ParticipantRepository $pr, $id, Request $req): Response
    {
        $user = $pr->find($id);

        if ($this->isCsrfTokenValid('modify' . $user->getId(), $req->get('_token'))) {
            return $this->redirectToRoute('user_modifier');
        }
        return $this->render('user/monProfil.html.twig', [
            'user'=>$user
        ]);
    }
}