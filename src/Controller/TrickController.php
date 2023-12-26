<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    #[Route(
        path: '/tricks',
        name: 'app_tricks',
        methods: ['GET']
    )]
    public function listTricks(
        TrickRepository $tricksRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        $query = $tricksRepository->findAll();

        $tricks = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('pages/trick/list_tricks.html.twig', [
            'tricks' => $tricks
        ]);
    }


    #[Route(
        path: '/trick/{id}',
        name: 'app_trick_one',
        requirements: ['id' => '\d+'],
        methods: ['GET']
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
        name: 'app_trick_create',
        methods: ['GET', 'POST']

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
            return $this->redirectToRoute(route: 'app_trick_one', parameters: ['id' => $trick->getId()]);
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
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST']
    )]
    public function editTrick(
        TrickRepository $trickRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        $id
//        ,
//        UploaderService $uploaderService,
//        MailerService $mailer
    ): Response
    {
            $trick = $trickRepository->find($id);
            $form = $this->createForm(TrickType::class, $trick);
            $form->remove(name: 'created_at');
            $form->remove(name: 'updated_at');
            $form->remove(name: 'createdBy');
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var Trick $trick */
                $trick = $form->getData();
//                $photoFile = $form->get('photo')->getData();

//                if ($photoFile) {
//                    $directory = $this->getParameter('personne_directory');
//                    $personne->setImage($uploaderService->uploadFile($photoFile, $directory));
//                }

                $entityManager->persist($trick);
                $entityManager->flush();
                $this->addFlash('success', 'Votre trick a été mis à jour');
                return $this->redirectToRoute(route: 'app_tricks');
            }
            else {
                return $this->render('pages/trick/trick_edit.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
    }


    #[Route(
        path: '/trick/delete/{id}',
        name: 'app_trick_delete',
        requirements: ['id' => '\d+'],
        methods: ['GET']

    )]
    public function deleteTrick(
        $id,
        TrickRepository $trickRepository,
        EntityManagerInterface $entityManager
    ): Response
    {

        $trick = $trickRepository->find($id);

        if ($trick) {
            $entityManager->remove($trick);
            $entityManager->flush();
        }
        return $this->redirectToRoute(route: 'app_tricks');
    }

}
