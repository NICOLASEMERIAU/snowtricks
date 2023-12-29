<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{
    #[Route('/user/edit', name: 'app_user_edit')]
    public function index(
        Request                     $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface      $entityManager,
        SluggerInterface            $slugger
    ): Response
    {
        $form = $this->createForm(UserType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $plainPassword = $form->get('plainPassword')->getData();
            if (null !== $plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }
            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'image' field is not required
            // so the file must be processed only when a file is uploaded
                    if ($imageFile) {
                        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        // this is needed to safely include the file name as part of the URL
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                        // Move the file to the directory where images_profil are stored
                        try {
                            $imageFile->move(
                                $this->getParameter('images_profil_directory'),
                                $newFilename
                            );
                        } catch (FileException $e) {
                            // ... handle exception if something happens during file upload
                        }

                        // updates the 'imageFilename' property to store the file name
                        // instead of its contents
                        $user->setImageFilename($newFilename);
                    }
                $entityManager->flush();
                $this->addFlash('success', 'Votre profil a été mis à jour');
                return $this->redirectToRoute(route: 'app_tricks');
        } else {
                return $this->render('pages/user/user_form.html.twig', [
                    'form' => $form->createView(),
                ]);
        }
    }
}
