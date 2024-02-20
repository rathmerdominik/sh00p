<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\ShoppingCart;
use App\Model\CartDTO;
use App\Repository\ShoppingCartRepository;
use Doctrine\ORM\EntityManagerInterface;

class ShoppingCartService
{
    public function __construct(
        public EntityManagerInterface $entityManager,
        public ShoppingCartRepository $shoppingCartRepository,
        public CustomerService $customerService
    ){
    }

    /**
     * @return array<ShoppingCart>|string
     */
    public function getShoppingCarts(int $customer_id): array|string
    {
        $customer = $this->customerService->getCustomerById($customer_id);

        if(is_string($customer)) {
            return $customer;
        }

        return $customer->getShoppingCarts()->toArray();
    }

    public function getShoppingCartById(int $customer_id, int $cart_id): ShoppingCart|string
    {
        $cart = $this->shoppingCartRepository->find($cart_id);
        if(is_null($cart) || $cart->getCustomer()->getId() != $customer_id) {
            return ErrorMessage::CART_NOT_FOUND;
        }

        return $cart;
    }

    public function createShoppingCart(int $customer_id, CartDTO $cartDTO): ShoppingCart|string
    {
        $customer = $this->customerService->getCustomerById($customer_id);

        if(is_string($customer)) {
            return $customer;
        }

        $cart = new ShoppingCart();
        $cart->setName($cartDTO->name);
        $cart->setCustomer($customer);

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        return $cart;
    }

    public function editShoppingCart(int $customer_id, int $cart_id, CartDTO $cartDTO): ShoppingCart|string
    {
        $cart = $this->shoppingCartRepository->find($cart_id);
        if(is_null($cart) || $cart->getCustomer()->getId() != $customer_id) {
            return ErrorMessage::CART_NOT_FOUND;
        }

        $cart->setName($cartDTO->name);
        $this->entityManager->flush();

        return $cart;
    }

    public function deleteShoppingCart(int $customer_id, int $cart_id): null|string
    {
        $cart = $this->shoppingCartRepository->find($cart_id);
        if(is_null($cart) || $cart->getCustomer()->getId() != $customer_id) {
            return ErrorMessage::CART_NOT_FOUND;
        }

        $this->entityManager->remove($cart);
        $this->entityManager->flush();

        return null;
    }

}