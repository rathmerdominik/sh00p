<?php

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
        if($cart === null || $cart->getCustomer()->getId() != $customer_id) {
            return 'Cart not found';
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
        if($cart === null || $cart->getCustomer()->getId() != $customer_id) {
            return 'Cart not found';
        }

        $cart->setName($cartDTO->name);
        $this->entityManager->flush();

        return $cart;
    }

    public function deleteShoppingCart(int $customer_id, int $cart_id): null|string
    {
        $cart = $this->shoppingCartRepository->find($cart_id);
        if($cart === null || $cart->getCustomer()->getId() != $customer_id) {
            return 'Cart not found';
        }

        $this->entityManager->remove($cart);
        $this->entityManager->flush();

        return null;
    }

}