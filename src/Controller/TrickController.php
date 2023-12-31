<?php

namespace App\Controller;

use App\Entity\Commentary;
use App\Entity\Trick;
use App\Entity\Image;
use App\Form\CommentaryType;
use App\Form\TrickType;
use App\Repository\CommentaryRepository;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Repository\VideoRepository;
use App\Service\MailerService;
use App\Service\UploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
        path: '/trick/details/{name}',
        name: 'app_trick_one',
        methods: ['GET', 'POST']
    )]

    public function oneTrick(
        TrickRepository $tricksRepository,
        CommentaryRepository $commentaryRepository,
        VideoRepository $videoRepository,
        ImageRepository $imageRepository,
        $name,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $trick = $tricksRepository->findOneBy(
            criteria: ['name' => $name]
        );

        if ($trick->getId()) {
            $commentaries = $commentaryRepository->findBy(
                criteria: ['trick' => $trick->getId()],
                orderBy: ['updatedAt' => 'DESC']
            );
            $videos = $videoRepository->findBy(
                criteria: ['trick' => $trick->getId()]
            );
            $images = $imageRepository->findBy(
                criteria: ['trick' => $trick->getId()]
            );
        }

        $commentary = new Commentary();
        $commentaryForm = $this->createForm(CommentaryType::class, $commentary);
        $commentaryForm->remove(name: 'createdAt');
        $commentaryForm->remove(name: 'updatedAt');
        $commentaryForm->remove(name: 'user');
        $commentaryForm->remove(name: 'trick');

        $commentaryForm->handleRequest($request);
        if ($commentaryForm->isSubmitted() && $commentaryForm->isValid()) {
            /** @var Trick $trick */

            $commentary = $commentaryForm->getData();
            $commentary->setUser($this->getUser());
            $commentary->setTrick($trick);


            $entityManager->persist($commentary);
            $entityManager->flush();
            return $this->redirectToRoute(route: 'app_trick_one', parameters: ['name' => $trick->getName()]);
        }
        else {

            return $this->render('pages/trick/one_trick.html.twig', [
                'trick' => $trick,
                'commentaryForm' => $commentaryForm->createView(),
                'commentaries' => $commentaries,
                'videos' => $videos,
                'images' => $images
            ]);
        }




    }

    #[Route(
        path: '/trick/create',
        name: 'app_trick_create',
        methods: ['GET', 'POST']

)]

    public function createTrick(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerService $mailer,
        UploaderService $uploaderService
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->remove(name: 'createdAt');
        $form->remove(name: 'updatedAt');
        $form->remove(name: 'user');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Trick $trick */

            $trick = $form->getData();
            $trick->setUser($this->getUser());

            /** @var UploadedFile $mainImageFile */
            $mainImageFile = $form->get('mainImageFile')->getData();
            // this condition is needed because the 'image' field is not required
            // so the file must be processed only when a file is uploaded
            if ($mainImageFile) {
                $directory = $this->getParameter(name: 'images_trick_directory');
                $trick->setImageName($uploaderService->uploadFile($mainImageFile, $directory));
            }

            $entityManager->persist($trick);
            $entityManager->flush();
            $message = " a été ajouté avec succès dans les SNowTricks";
            $mailMessage = $trick->getName().' '.$message;
            $mailer->sendEmail(content: $mailMessage);
            $this->addFlash('success', 'Votre trick a été ajouté');
            return $this->redirectToRoute(route: 'app_trick_one', parameters: ['name' => $trick->getName()]);
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
        ImageRepository $imageRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        int $id,
        UploaderService $uploaderService
    ): Response
    {
            $trick = $trickRepository->find($id);
            $images = $imageRepository->findBy(
                criteria: [ 'trick' => $trick->getId()]
            );

            $form = $this->createForm(TrickType::class, $trick);
            $form->remove(name: 'createdAt');
            $form->remove(name: 'updatedAt');
            $form->remove(name: 'user');
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var Trick $trick */
                $trick = $form->getData();

                /** @var UploadedFile $mainImageFile */
                $mainImageFile = $form->get('mainImageFile')->getData();
                // this condition is needed because the 'image' field is not required
                // so the file must be processed only when a file is uploaded
                if ($mainImageFile) {
                    $directory = $this->getParameter(name: 'images_trick_directory');
                    $trick->setImageName($uploaderService->uploadFile($mainImageFile, $directory));
                }

                // essai de récupération des fichiers images envoyés
                foreach ($form->get('images') as $formChild) {
                    $uploadedImages = $formChild->get('name')->getData();
                    foreach ($uploadedImages as $uploadedImage) {
                        $image = new Image();
                        $image->setTrick($trick);
                        $directory = $this->getParameter(name: 'images_trick_directory');
                        $newFileName = $uploaderService->uploadFile($uploadedImage, $directory);
                        $image->setName($newFileName);
                        $entityManager->persist($image);
                    }
                }
                // this condition is needed because the 'image' field is not required
                // so the file must be processed only when a file is uploaded
                if ($mainImageFile) {
                    $directory = $this->getParameter(name: 'images_trick_directory');
                    $trick->setImageName($uploaderService->uploadFile($mainImageFile, $directory));
                }


                $entityManager->persist($trick);
                $entityManager->flush();
                $this->addFlash('success', 'Votre trick a été mis à jour');
                return $this->redirectToRoute(route: 'app_trick_one', parameters: ['name' => $trick->getName()]);
            }
            else {
                return $this->render('pages/trick/trick_edit.html.twig', [
                    'form' => $form->createView(),
                    'images' => $images
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
