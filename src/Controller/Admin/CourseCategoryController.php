<?php

namespace App\Controller\Admin;

use App\Domain\Course\Entity\CourseCategory;
use App\Domain\Course\Repository\CourseCategoryRepository;
use App\Domain\Gym\Repository\GymRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/course-categories')]
class CourseCategoryController extends AbstractController
{
    #[Route('/', name: 'admin_course_categories')]
    public function index(
        CourseCategoryRepository $categoryRepo
    ): Response {
        $categories = $categoryRepo->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/course_categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/create', name: 'admin_course_category_create')]
    public function create(
        Request $request,
        CourseCategoryRepository $categoryRepo,
        GymRepository $gymRepo
    ): Response {
        if ($request->isMethod('POST')) {
            $category = new CourseCategory();
            $category->setName($request->request->get('name'));
            $category->setColor($request->request->get('color'));

            // Associa alla palestra dell'admin loggato
            $user = $this->getUser();
            $gyms = $gymRepo->createQueryBuilder('g')
                ->join('g.admins', 'a')
                ->where('a = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();

            if (empty($gyms)) {
                $this->addFlash('error', 'Nessuna palestra associata al tuo account.');
                return $this->redirectToRoute('admin_course_categories');
            }

            $category->setGym($gyms[0]);

            $categoryRepo->save($category, true);

            $this->addFlash('success', 'Categoria creata con successo.');
            return $this->redirectToRoute('admin_course_categories');
        }

        return $this->render('admin/course_categories/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'admin_course_category_edit')]
    public function edit(
        int $id,
        Request $request,
        CourseCategoryRepository $categoryRepo
    ): Response {
        $category = $categoryRepo->find($id);

        if (!$category) {
            $this->addFlash('error', 'Categoria non trovata.');
            return $this->redirectToRoute('admin_course_categories');
        }

        if ($request->isMethod('POST')) {
            $category->setName($request->request->get('name'));
            $category->setColor($request->request->get('color'));

            $categoryRepo->save($category, true);

            $this->addFlash('success', 'Categoria aggiornata con successo.');
            return $this->redirectToRoute('admin_course_categories');
        }

        return $this->render('admin/course_categories/edit.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_course_category_delete', methods: ['POST'])]
    public function delete(
        int $id,
        CourseCategoryRepository $categoryRepo
    ): Response {
        $category = $categoryRepo->find($id);

        if (!$category) {
            $this->addFlash('error', 'Categoria non trovata.');
            return $this->redirectToRoute('admin_course_categories');
        }

        // Verifica se ci sono corsi che usano questa categoria
        if ($category->getId()) {
            // TODO: controllare se ci sono corsi associati
            $categoryRepo->remove($category, true);
            $this->addFlash('success', 'Categoria eliminata con successo.');
        }

        return $this->redirectToRoute('admin_course_categories');
    }
}
