<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes  as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Request;

#[OA\Tag(name: 'Products')]
class ProductController extends AbstractController
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
     * List the products in db.
     *
     */
    #[Route('/api/products', methods: ['GET'])]
    public function getAllProducts(EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $products = $entityManager
                ->getRepository(Product::class)
                ->findAll();

            $data = [];

            foreach ($products as $product) {
                $data[] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'category' => $product->getCategory() ? [
                        'id' => $product->getCategory()->getId(),
                        'name' => $product->getCategory()->getName(),
                        'description' => $product->getCategory()->getDescription()
                    ] : null,
                ];
            }

            return $this->responseSuccess("", $data);
        } catch (\Throwable $th) {
            return $this->responseError("Error", null, 400);
        }
    }

    /**
     * Get the product by id from db.
     *
     */
    #[Route('/api/products/{id}/', methods: ['GET'])]
    public function getProducts(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        try {
            $product = $entityManager
                ->getRepository(Product::class)
                ->find($id);

            $category = $entityManager
                ->getRepository(Category::class)
                ->find($product->getCategory()->getId());

            $data = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'category' => [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'description' => $category->getDescription(),
                ]
            ];

            return $this->responseSuccess("", $data);
        } catch (\Throwable $th) {
            return $this->responseError("Error", null, 400);
        }
    }

    /**
     * Create a product.
     *
     */
    #[Route('/api/products', methods: ['POST'])]
    public function createProduct(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent());

            $category = $entityManager
                ->getRepository(Category::class)
                ->find($parameters->idCategory);

            $product = new Product();
            $product->setName($parameters->name);
            $product->setDescription($parameters->description);
            $product->setPrice($parameters->price);
            $product->setCategory($category);
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->responseSuccess('', ['id' => $product->getId()]);
        } catch (\Throwable $th) {
            return $this->responseError("Error", null, 400);
        }
    }

    /**
     * Update a product
     *
     */
    #[Route('/api/products', methods: ['PUT'])]
    public function updateProduct(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent());

            $product = $entityManager
                ->getRepository(Product::class)
                ->find($parameters->id);

            $category = null;

            if ($parameters->category) {
                $category = $entityManager
                    ->getRepository(Category::class)
                    ->find($parameters->idCategory);
            }

            $product->setName($parameters->name);
            $product->setDescription($parameters->description);
            $product->setPrice($parameters->price);
            $product->setCategory($category);
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->responseSuccess('', $parameters);
        } catch (\Throwable $th) {
            return $this->responseError("Error", null, 400);
        }
    }

    /**
     * Delete list of products
     *
     */
    #[Route('/api/products', methods: ['DELETE'])]
    public function deleteProducts() {}

    /**
     * Delete a product with an id
     *
     */
    #[Route('/api/products/{id}/', methods: ['DELETE'])]
    public function deleteProduct(EntityManagerInterface $entityManager, int $id)
    {
        try {
            $product = $entityManager->getRepository(Product::class)->find($id);
            $entityManager->remove($product);
            $entityManager->flush();
            return $this->responseSuccess('', ['id' => $id]);
        } catch (\Throwable $th) {
            return $this->responseError("Error", null, 400);
        }
    }
}
