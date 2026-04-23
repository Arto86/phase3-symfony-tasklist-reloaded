<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Repository\FolderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/task'), IsGranted('IS_AUTHENTICATED_FULLY')]
final class TaskController extends AbstractController
{
    #[Route(name: 'app_task_index', methods: ['GET'])]
    public function index(Request $request, TaskRepository $taskRepository, FolderRepository $folderRepository): Response
    {
        $folders = $folderRepository->findFolderByUser($this->getUser());
        
        $status = $request->query->get('status');
        $priority = $request->query->get('priority');
        
        $tasks = $taskRepository->findByFilters($this->getUser(), $status, $priority);
        // $tasks = $taskRepository->findByUserOrderedByStatus($this->getUser());

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'folders' => $folders
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, FolderRepository $folderRepository): Response
    {
        $folders = $folderRepository->findFolderByUser($this->getUser());

        $task = new Task();

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
            $task->setOwner($user);

            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'folders' => $folders,
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager, FolderRepository $folderRepository): Response
    {
        $folders = $folderRepository->findFolderByUser($this->getUser());

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'folders' => $folders,
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/pin', name: 'app_task_pin', methods: ['GET'])]
    public function pinTask(Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($task->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('C\'est pas ta tâche, ça !');
        }

        $task->setIsPinned(!$task->isPinned());

        $entityManager->flush();

        return $this->redirectToRoute('app_task_index');
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }
}
