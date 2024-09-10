<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes  as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Request;

#[OA\Tag(name: 'Categories')]
class CategoryController extends AbstractController
{
    function responseSuccess(string $message, $data = null): JsonResponse
    {
        return new JsonResponse([
            "success" => true,
            "message" => $message,
            "data" => $data,
            "statusCode" => 200
        ], 200);
    }

    function responseError(string $message, $data = null, int $error = 400): JsonResponse
    {
        return new JsonResponse([
            "success" => false,
            "message" => $message,
            "data" => $data,
            "statusCode" => $error
        ], $error);
    }


    /**
     * List the categories in db.
     *
     */
    #[Route('/api/categories/', methods: ['GET'])]
    public function getAllCategories(EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $categories = $entityManager
                ->getRepository(Category::class)
                ->findAll();

            $data = [];

            foreach ($categories as $category) {
                $data[] = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'description' => $category->getDescription(),
                ];
            }

            return $this->responseSuccess('', $data);
        } catch (\Throwable $th) {
            return $this->responseError("Error", null, 400);
        }
    }

    /**
     * Get the category by id from db.
     *
     */
    #[Route('/api/categories/{id}/', methods: ['GET'])]
    public function getCategory(EntityManagerInterface $entityManager, int $id)
    {
        try {
            $category = $entityManager
                ->getRepository(Category::class)
                ->find($id);

            $data = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'description' => $category->getDescription(),
            ];

            return $this->responseSuccess('', $data);
        } catch (\Throwable $th) {
            return $this->responseError("Error", null, 400);
        }
    }

    /**
     * Create a category.
     *
     */
    #[Route('/api/categories', methods: ['POST'])]
    public function createCategory(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent());
            $category = new Category();
            $category->setName($parameters->name);
            $category->setDescription($parameters->description);
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->responseSuccess('', ['id' => $category->getId()]);
        } catch (\Throwable $th) {
            return $this->responseError("Error", null, 400);
        }
    }

    /**
     * Update a ctegory
     *
     */
    #[Route('/api/categories', methods: ['PUT'])]
    public function updateCategory(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent());
            $category = $entityManager
                ->getRepository(Category::class)
                ->find($parameters->id);

            $category->setName($parameters->name);
            $category->setDescription($parameters->description);
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->responseSuccess(
                '',
                $category
            );
        } catch (\Throwable $th) {
            return $this->responseError("Error", null, 400);
        }
    }

    /**
     * Delete list of categories
     *
     */
    #[Route('/api/categories', methods: ['DELETE'])]
    public function deleteCategories() {}

    /**
     * Delete a category with an id
     *
     */
    #[Route('/api/categories/{id}/', methods: ['DELETE'])]
    public function deleteCategory(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        try {
            $categories = $entityManager->getRepository(Category::class)->find($id);
            $entityManager->remove($categories);
            $entityManager->flush();
            return $this->responseSuccess('', ['id' => $id]);
        } catch (\Throwable $th) {
            return $this->responseError("Error", null, 400);
        }
    }
}
