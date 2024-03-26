<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Validator\Demo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;


class RecipeController extends AbstractController
{

    #[Route('/demo')]
    public function demo(Demo $demo)
    {
        dd($demo);
    }

    #[Route('/recettes', name: 'recipe.index')]
    public function index(Request $request, RecipeRepository $repository): Response
    {
        $recipes = $repository->findWithDurationLowerThan(60);

        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes
        ]);
    }

    #[Route('recettes/{slug}/{id}', name: 'recipe.show', requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id, RecipeRepository $repository): Response
    {
        $recipe = $repository->find($id);
        if ($recipe->getSlug() == !$slug) {
            return $this->redirectToRoute('recipe.show', ['slug' => $recipe->getSlug(), 'id' => $recipe->getId()]);
        }
        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe
        ]);
    }

    #[Route('recettes/{id}/edit', name: 'recipe.edit', requirements: ['id' => Requirement::DIGITS])]
    public function editRecipe(Recipe $recipe, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('thumbnailFile')->getData();
            $filename = $recipe->getId() . '.' . $file->getClientOriginalExtension();
            $file->move($this->getParameter("kernel.project_dir") . '/public/images/recipes', $filename);
            $recipe->setThumbnail($filename);
            $recipe->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('succes', "c'est good");
            return $this->redirectToRoute('recipe.index');
        }
        return $this->render('recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form
        ]);
    }

    #[Route('recettes/add', name: 'recipe.add')]
    public function addRecipe(Request $request, EntityManagerInterface $em)
    {

        // J'instancie un nouveau formulaire via RecipeType qui remplira ma recette 
        $form = $this->createForm(RecipeType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Je crée un nouvelle recette vide
            $recipe = new Recipe();
            $data = $form->getData();
            $recipe->setTitle($data["title"]);
            $recipe->setSlug($data["slug"]);
            $recipe->setText($data["text"]);
            $recipe->setDuration($data["duration"]);
            $recipe->setCategory($data["category_id"]);
            $recipe->setCreatedAt(new \DateTimeImmutable());
            $recipe->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('Succes', 'Nouvelle recette ajouter');
            return $this->redirectToRoute('recipe.index');
        }
        return $this->render('recipe/add.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('recettes/{id}/delete', name: 'recipe.delete', requirements: ['id' => Requirement::DIGITS])]
    public function deleteRecipe(Recipe $recipe, EntityManagerInterface $em)
    {
        $em->remove($recipe);
        $em->flush();
        $this->addFlash('Succes', 'Recette supprimer');
        return $this->redirectToRoute('recipe.index');
    }
}
