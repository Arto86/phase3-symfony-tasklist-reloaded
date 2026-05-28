<?php

namespace App\Controller;

use App\Entity\Folder;
use App\Form\FolderType;
use App\Repository\FolderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/folder')]
final class FolderController extends AbstractController
{
    #[Route(name: 'app_folder_index', methods: ['GET'])]
    public function index(FolderRepository $folderRepository): Response
    {
        return $this->render('folder/index.html.twig', [
            'folders' => $folderRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_folder_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, FolderRepository $folderRepository): Response
    {
        $folders = $folderRepository->findFolderByUser($this->getUser());


        $folder = new Folder();
        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
            $folder->setOwnerId($user);

            $entityManager->persist($folder);
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('folder/new.html.twig', [
            'folders' => $folders,
            'folder' => $folder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_folder_show', methods: ['GET'])]
    public function show(Folder $folder, FolderRepository $folderRepository): Response
    {
        $folders = $folderRepository->findFolderByUser($this->getUser());

        return $this->render('folder/show.html.twig', [
            'folders' => $folders,
            'folder' => $folder,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_folder_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Folder $folder, EntityManagerInterface $entityManager, FolderRepository $folderRepository): Response
    {
        $folders = $folderRepository->findFolderByUser($this->getUser());

        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('folder/edit.html.twig', [
            'folders' => $folders,
            'folder' => $folder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_folder_delete', methods: ['POST'])]
    public function delete(Request $request, Folder $folder, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$folder->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($folder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }
}
