<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use App\Repository\TricksGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    #[Route('/tricks', name: 'app_tricks')]
    public function listTricks(TrickRepository $tricksRepository): Response
    {
        $tricks = $tricksRepository->findAll();
        return $this->render('pages/trick/list_tricks.html.twig', [
            'tricks' => $tricks
        ]);
    }


    #[Route(
        path: '/trick/{id}',
        name: 'app_trick_one',
        requirements: ['id' => '\d+']
    )]

    public function oneTrick(
        TrickRepository $tricksRepository,
        $id
    ): Response
    {
        $trick = $tricksRepository->find($id);
        $name_group = $trick->getTrickgroup()->getNameGroup();
        $created_by_name = $trick->getCreatedBy()->getUsername();
        return $this->render('pages/trick/one_trick.html.twig', [
            'trick' => $trick,
            'name_group' => $name_group,
            'created_by_name' => $created_by_name
        ]);
    }

    #[Route(
        path: '/trick/create',
        name: 'app_trick_create'
    )]

    public function createTrick(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->remove(name: 'created_at');
        $form->remove(name: 'updated_at');
        $form->remove(name: 'createdBy');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Trick $trick */

            $trick = $form->getData();
            $trick->setCreatedBy($this->getUser());


            $entityManager->persist($trick);
            $entityManager->flush();
            $this->addFlash('success', 'Votre trick a été ajouté');
            return $this->redirectToRoute(route: 'app_tricks');
        }
        else {

            return $this->render('pages/trick/create_trick.html.twig', [
                'form' => $form->createView()
            ]);
        }

    }

    #[Route(
        path: '/trick/edit/{id}',
        name: 'app_trick_edit',
        requirements: ['id' => '\d+']
    )]
    public function editTrick(
        TrickRepository $trickRepository,
        $id
//        ,
//        UploaderService $uploaderService,
//        MailerService $mailer
    ): Response
    {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $trick = $trickRepository->find($id);

        $form = $this->createForm(PersonneType::class, $personne);
        $form->remove('createdAt');
        $form->remove('updatedAt');
        //mon formulaire va aller traiter la requete
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            //est ce que le formulaire a été soumis?
            //si oui
            //ajouter l'objet personne dans la bdd

            $photoFile = $form->get('photo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photoFile) {
                $directory = $this->getParameter('personne_directory');
                $personne->setImage($uploaderService->uploadFile($photoFile, $directory));
            }

            //afficher un message de succès
            if($new) {
                $message = " a été ajouté avec succès";
                $personne->setCreatedBy($this->getUser());
            } else {
                $message = " a été mis à jour avec succès";
            }
            $manager = $doctrine->getManager();
            $manager->persist($personne);
            $manager->flush();

            if($new) {
                $addPersonneEvent = new AddPersonneEvent($personne);
//                ON va maintenant dispatcher cet évènement
                $this->dispatcher->dispatch($addPersonneEvent, AddPersonneEvent::ADD_PERSONNE_EVENT);
            }


            $this->addFlash(
                type: 'success',
                message: $personne->getName(). $message
            );

            //si non rediriger vers la liste des personnes
            return $this->redirectToRoute(route: 'personne.list');


        } else {
            //si non , on affiche le formulaire


            return $this->render('personne/add-personne.html.twig', [
                'form' => $form->createView()
            ]);
        }


    }

}
