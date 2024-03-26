<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/recettes/category', name: 'category.index')]
    public function index(CategoryRepository $repository): Response
    {
        $categories = $repository->findCategories();
        return $this->render('category/index.html.twig', [
            "categories" => $categories
        ]);
    }

    #[Route('category/add', name: 'category.add', methods: ["POST", "GET"])]
    public function addCategory(Request $request, EntityManagerInterface $em)
    {

        // J'instancie un nouveau formulaire via RecipeType qui remplira ma recette 
        $form = $this->createForm(CategoryType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Je crée un nouvelle recette vide
            $data = $form->getData();
            $category = new Category($data["category"], $data["slug"]);
            $em->persist($category);
            $em->flush();
            $this->addFlash('Succes', 'Nouvelle recette ajouter');
            return $this->redirectToRoute('category.index');
        }
        return $this->render('category/add.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('category/{id}/delete', name: 'category.delete')]
    public function delete(Category $category, EntityManagerInterface $em)
    {

        $em->remove($category);
        $em->flush();
        $this->addFlash('Succes', 'category supprimer');
        return $this->redirectToRoute('category.index');
    }

    #[Route('category/{id}', name: 'recipe.category.test')]
    public function filterRecipeByCategory(int $id, CategoryRepository $repository)
    {
        $category = $repository->find($id);
        $recipes = $category->getRecipes();
        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes
        ]);
    }
}
