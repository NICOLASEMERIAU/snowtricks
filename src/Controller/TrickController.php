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
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

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
        path: '/trick/details/{slug}',
        name: 'app_trick_one',
        methods: ['GET', 'POST']
    )]

    public function oneTrick(
        TrickRepository $tricksRepository,
        CommentaryRepository $commentaryRepository,
        VideoRepository $videoRepository,
        ImageRepository $imageRepository,
        $slug,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $trick = $tricksRepository->findOneBy(
            criteria: ['slug' => $slug]
        );

        if ($trick) {


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
                return $this->redirectToRoute(route: 'app_trick_one', parameters: ['slug' => $trick->getSlug()]);
            } else {

                return $this->render('pages/trick/one_trick.html.twig', [
                    'trick' => $trick,
                    'commentaryForm' => $commentaryForm->createView(),
                    'commentaries' => $commentaries,
                    'videos' => $videos,
                    'images' => $images
                ]);
            }
        } else {

            $this->addFlash('danger', "Cette page n'existe pas.");

            return $this->redirectToRoute(route: 'app_tricks');

        }



    }

    #[Route(
        path: '/tricks/load_more',
        name: 'app_tricks_load_more',
        methods: ['GET']
    )]
    public function loadMore(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) : JsonResponse
    {
       $tricks = $entityManager->getRepository(Trick::class)->findAll();




       return new JsonResponse(data: $serializer->serialize($tricks, 'json', ['groups' => ['trick']]), json: true);

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
            $newSlug = (new Slugify())->slugify($trick->getName());
            $trick->setSlug($newSlug);

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


            $entityManager->persist($trick);
            $entityManager->flush();
            $message = " a été ajouté avec succès dans les SNowTricks";
            $mailMessage = $trick->getName().' '.$message;
            $mailer->sendEmail(content: $mailMessage);
            $this->addFlash('success', 'Votre trick a été ajouté');
            return $this->redirectToRoute(route: 'app_trick_one', parameters: ['slug' => $trick->getSlug()]);
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
        if ($trick) {
            if ($trick->getUser() !== $this->getUser()) {
                throw $this->createAccessDeniedException();
            } else {
                $images = $imageRepository->findBy(
                    criteria: ['trick' => $trick->getId()]
                );
                $form = $this->createForm(TrickType::class, $trick);
                $form->remove(name: 'createdAt');
                $form->remove(name: 'updatedAt');
                $form->remove(name: 'user');
                $form->handleRequest($request);


                if ($form->isSubmitted() && $form->isValid()) {
                    /** @var Trick $trick */
                    $trick = $form->getData();
                    $newSlug = (new Slugify())->slugify($trick->getName());
                    $trick->setSlug($newSlug);

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

                    $entityManager->persist($trick);
                    $entityManager->flush();
                    $this->addFlash('success', 'Votre trick a été mis à jour');
                    return $this->redirectToRoute(route: 'app_trick_one', parameters: ['slug' => $trick->getSlug()]);
                }
                else {
                    return $this->render('pages/trick/trick_edit.html.twig', [
                        'form' => $form->createView(),
                        'images' => $images,
                        'trick' => $trick
                    ]);
                }
            }
        }
    }


    #[Route(
        path: '/trick/delete/{id}',
        name: 'app_trick_delete',
        requirements: ['id' => '\d+'],
        methods: ['GET']

    )]
    #[IsGranted('ROLE_USER')]
    public function deleteTrick(
        $id,
        TrickRepository $trickRepository,
        EntityManagerInterface $entityManager
    ): Response
    {

        $trick = $trickRepository->find($id);
        if ($trick) {
            if ($trick->getUser() !== $this->getUser()) {
                throw $this->createAccessDeniedException();
            }
            $entityManager->remove($trick);
            $entityManager->flush();
        }
        return $this->redirectToRoute(route: 'app_tricks');
    }

}
