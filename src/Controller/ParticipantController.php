<?php

namespace App\Controller;

use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


class ParticipantController extends AbstractController
{
    #[Route('user/modify/{id}', name: 'user_modifier')]
    public function modifierProfil(UserPasswordHasherInterface $userPasswordHasher, Request $req, ParticipantRepository $pr, $id, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $idUserConnecte = $this->getUser()->getId();
        if ($idUserConnecte == $id) {
            $user = $pr->find($id);

            $userForm = $this->createForm(ParticipantType::class, $user);
            $userForm->handleRequest($req);


            if ($userForm->isSubmitted() && $userForm->isValid()) {
                $photoFile = $userForm->get('photo')->getData();

                if ($photoFile) {
                    $originalFilename = pathinfo($photoFile->getCLientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
                    $user->setImage($newFilename);

                    try {
                        $photoFile->move(
                            $this->getParameter('photos_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Picture not upload');
                    }

                }
                if ($userForm->get('newPassword')->getData() === $userForm->get('password')->getData()) {
                    if ($userForm->get('enregistrer')->isClicked()) {
                        $user->setPassword(
                            $userPasswordHasher->hashPassword(
                                $user,
                                $userForm->get('password')->getData()
                            )
                        );
                        $em->flush();
                        return $this->redirectToRoute('user_monProfil', ['id' => $user->getId()]);
                    } else {
                        return $this->redirectToRoute('user_monProfil', ['id' => $user->getId()]);
                    }
                } else {
                    $this->addFlash('error', 'Les 2 mots de passe doivent être identiques');
                    return $this->redirectToRoute('user_monProfil', ['id' => $user->getId()]);
                }

            }

            return $this->render('user/modifier.html.twig', [
                    'user' => $user,
                    'userForm' => $userForm->createView(),]
            );
        } else {
        //    throw $this->createAccessDeniedException();
            $this->addFlash('error', 'Accès refusé !');
            return $this->render('error/pirate.html.twig',

            );
        }
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