<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\CartProductDTO;
use App\Service\CartProductService;
use App\Service\EntitySerializerService;
use App\Service\ErrorMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/customers/{customer_id}/carts/{cart_id}/products')]
class CartProductController extends AbstractController
{
    public function __construct(
        public CartProductService $cartProductService,
        public EntitySerializerService $entitySerializerService
    ) {}

    #[Route('', name: 'app_cart_products_get', methods: ['GET'], format: 'json')]
    public function getCartProducts(
        int $customer_id,
        int $cart_id
    ): JsonResponse
    {
        $cartProducts = $this->cartProductService->getCartProducts($customer_id, $cart_id);
        return new JsonResponse($this->entitySerializerService->serializeEntity($cartProducts), Response::HTTP_OK, json: true);
    }

    #[Route('/{cart_product_id}', name: 'app_cart_products_get_by_id', methods: ['GET'], format: 'json')]
    public function getCartProductById(
        int $customer_id,
        int $cart_id,
        int $cart_product_id
    ): JsonResponse {
        $cartProduct = $this->cartProductService->getCartProductById($customer_id, $cart_id, $cart_product_id);
        if (is_string($cartProduct)) {
            return new JsonResponse(['error' => $cartProduct], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($this->entitySerializerService->serializeEntity($cartProduct), Response::HTTP_OK, json: true);
    }

    #[Route('', name: 'app_cart_products_create', methods: ['POST'], format: 'json')]
    public function addCartProductToCart(
        #[MapRequestPayload] CartProductDTO $cartProductDTO,
        int $customer_id,
        int $cart_id,
    ): JsonResponse {
        $cartProduct = $this->cartProductService->addCartProductToCart($customer_id, $cart_id, $cartProductDTO);
        try {
            return match ($cartProduct) {
                ErrorMessage::AMOUNT_EQUAL_OR_LOWER_THAN_ZERO, ErrorMessage::AMOUNT_GREATER_THAN_STOCK => new JsonResponse(['error' => $cartProduct], Response::HTTP_BAD_REQUEST),
                ErrorMessage::PRODUCT_NOT_FOUND, ErrorMessage::PRODUCT_IN_CART_NOT_FOUND, ErrorMessage::CART_NOT_FOUND => new JsonResponse(['error' => $cartProduct], Response::HTTP_NOT_FOUND),
            };
        } catch (\UnhandledMatchError) {
            return new JsonResponse($this->entitySerializerService->serializeEntity($cartProduct), Response::HTTP_CREATED, json: true);
        }
    }

    #[Route('/{cart_product_id}', name: 'app_cart_products_edit', methods: ['PATCH'], format: 'json')]
    public function editCartProduct(
        #[MapRequestPayload] CartProductDTO $customerDto,
        int $customer_id,
        int $cart_id,
        int $cart_product_id
    ): JsonResponse {
        $cartProduct = $this->cartProductService->editCartProduct($customer_id, $cart_id, $cart_product_id, $customerDto);
        try {
            return match ($cartProduct) {
                ErrorMessage::AMOUNT_EQUAL_OR_LOWER_THAN_ZERO, ErrorMessage::AMOUNT_GREATER_THAN_STOCK => new JsonResponse(['error' => $cartProduct], Response::HTTP_BAD_REQUEST),
                ErrorMessage::PRODUCT_NOT_FOUND, ErrorMessage::PRODUCT_IN_CART_NOT_FOUND, ErrorMessage::CART_NOT_FOUND => new JsonResponse(['error' => $cartProduct], Response::HTTP_NOT_FOUND),
            };
        } catch (\UnhandledMatchError) {
            return new JsonResponse($this->entitySerializerService->serializeEntity($cartProduct), Response::HTTP_CREATED, json: true);
        }
    }

    #[Route('/{cart_product_id}', name: 'app_cart_products_delete', methods: ['DELETE'], format: 'json')]
    public function deleteCartProduct(
        int $customer_id,
        int $cart_id,
        int $cart_product_id
    ): JsonResponse
    {
        $response = $this->cartProductService->deleteCartProduct($customer_id, $cart_id, $cart_product_id);
        if (is_string($response)) {
            return new JsonResponse(['error' => $response], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse('', Response::HTTP_NO_CONTENT, json: true);
    }
}
