<?php

namespace App\Controller;


use App\Form\TaskType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Task;
use App\Entity\User;
use App\Security\Voter\TaskVoter;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task_list')]
    public function listAction(EntityManagerInterface $entityManager)
    {
        return $this->render('task/list.html.twig', [
            'tasks' => $entityManager->getRepository(Task::class)->findAll(),
        ]);
    }

    #[Route('/tasks/create', name: 'task_create')]
    public function createAction(Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            if (!$user) {
                throw new \LogicException('Aucun utilisateur authentifié.');
            }

            $task->setUser($user);

            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    public function editAction(
        Task $task,
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $this->denyAccessUnlessGranted(
            TaskVoter::EDIT,
            $task
        );

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/toggle', name: 'task_toggle')]
    public function toggleTaskAction(
        Task $task,
        EntityManagerInterface $entityManager
    ) {

        $this->denyAccessUnlessGranted(
            TaskVoter::TOGGLE,
            $task
        );

        $task->toggle(!$task->isDone());
        $entityManager->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete')]
    public function deleteTaskAction(Task $task, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(TaskVoter::DELETE, $task);

        $entityManager->remove($task);
        $entityManager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
