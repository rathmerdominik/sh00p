<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\CartDTO;
use App\Service\EntitySerializerService;
use App\Service\ShoppingCartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/customers/{customer_id}/carts')]
class ShoppingCartController extends AbstractController
{
    public function __construct(
        public ShoppingCartService $shoppingCartService,
        public EntitySerializerService $entitySerializerService
    ) {}

    #[Route('', name: 'app_carts_get', methods: ['GET'], format: 'json')]
    public function getShoppingCarts(
        int $customer_id
    ): JsonResponse
    {
        $shoppingCarts = $this->shoppingCartService->getShoppingCarts($customer_id);
        return new JsonResponse($this->entitySerializerService->serializeEntity($shoppingCarts), Response::HTTP_OK, json: true);
    }

    #[Route('/{cart_id}', name: 'app_carts_get_by_id', methods: ['GET'], format: 'json')]
    public function getShoppingCartById(
        int $customer_id,
        int $cart_id,
    ): JsonResponse {
        $shoppingCarts = $this->shoppingCartService->getShoppingCartById($customer_id, $cart_id);
        if (is_string($shoppingCarts)) {
            return new JsonResponse(['error' => $shoppingCarts], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($this->entitySerializerService->serializeEntity($shoppingCarts), Response::HTTP_OK, json: true);
    }

    #[Route('', name: 'app_carts_create', methods: ['POST'], format: 'json')]
    public function createShoppingCart(
        #[MapRequestPayload] CartDTO $cartDto,
        int $customer_id
    ): JsonResponse {
        $shoppingCart = $this->shoppingCartService->createShoppingCart($customer_id, $cartDto);
        return new JsonResponse($this->entitySerializerService->serializeEntity($shoppingCart), Response::HTTP_CREATED, json: true);
    }

    #[Route('/{cart_id}', name: 'app_carts_edit', methods: ['PATCH'], format: 'json')]
    public function editShoppingCart(
        #[MapRequestPayload] CartDTO $cartDto,
        int $customer_id,
        int $cart_id
    ): JsonResponse {
        $shoppingCart = $this->shoppingCartService->editShoppingCart($customer_id, $cart_id, $cartDto);
        if (is_string($shoppingCart)) {
            return new JsonResponse(['error' => $shoppingCart], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($this->entitySerializerService->serializeEntity($shoppingCart), Response::HTTP_OK, json: true);
    }

    #[Route('/{cart_id}', name: 'app_carts_delete', methods: ['DELETE'], format: 'json')]
    public function deleteShoppingCart(
        int $cart_id,
        int $customer_id
    ): JsonResponse
    {
        $shoppingCart = $this->shoppingCartService->deleteShoppingCart($customer_id, $cart_id);
        if (is_string($shoppingCart)) {
            return new JsonResponse(['error' => $shoppingCart], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse('', Response::HTTP_NO_CONTENT, json: true);
    }
}
