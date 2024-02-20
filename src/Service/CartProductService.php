<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\CartProduct;
use App\Model\CartProductDTO;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class CartProductService
{
    public function __construct(
     public EntityManagerInterface $entityManager,
     public ShoppingCartService $shoppingCartService,
     public ProductRepository $productRepository
    ){
    }

    /**
    * @return CartProduct[]
    */
    public function getCartProducts(int $customer_id, int $cart_id): array
    {
        $shoppingCart = $this->shoppingCartService->getShoppingCartById($cart_id, $customer_id);
        if (is_string($shoppingCart)) {
            return [];
        }
        return $shoppingCart->getCartProducts()->toArray();
    }

    public function getCartProductById(int $customer_id, int $cart_id, int $product_id): CartProduct|string
    {
        $shoppingCart = $this->shoppingCartService->getShoppingCartById($customer_id, $cart_id);
        if (is_string($shoppingCart)) {
            return $shoppingCart;
        }
        $cartProduct = $shoppingCart
            ->getCartProducts()
            ->filter(fn($product) => $product->getId() === $product_id)
            ->first();

        if(!$cartProduct) {
            return 'Product in cart not found';
        }
        return $cartProduct;
    }

    public function addCartProductToCart(int $customer_id, int $cart_id, CartProductDTO $cartProductDTO): CartProduct|string
    {
        $shoppingCart = $this->shoppingCartService->getShoppingCartById($customer_id, $cart_id);
        if (is_string($shoppingCart)) {
            return $shoppingCart;
        }

        $product = $this->productRepository->find($cartProductDTO->product_id);
        if (!$product) {
            return 'Product not found';
        }

        $cartProduct = new CartProduct();
        if($error = $this->verifyAmountInRange($cartProductDTO->amount, $product->getStock())) {
            return $error;
        }
        $cartProduct->setAmount($cartProductDTO->amount);

        $cartProduct->setProduct($product);
        $this->entityManager->persist($cartProduct);

        $shoppingCart->addCartProduct($cartProduct);
        $this->entityManager->persist($shoppingCart);

        $this->entityManager->flush();

        return $cartProduct;
    }

    public function editCartProduct(int $customer_id, int $cart_id, int $product_id, CartProductDTO $cartProductDTO): CartProduct|string
    {
        $cartProduct = $this->getCartProductById($customer_id, $cart_id, $product_id);
        if (is_string($cartProduct)) {
            return $cartProduct;
        }

        if($error = $this->verifyAmountInRange($cartProductDTO->amount, $cartProduct->getProduct()->getStock())) {
            return $error;
        }
        $cartProduct->setAmount($cartProductDTO->amount);
        $this->entityManager->flush();

        return $cartProduct;
    }

    public function deleteCartProduct(int $customer_id, int $cart_id, int $product_id): null|string
    {
        $cartProduct = $this->getCartProductById($customer_id, $cart_id, $product_id);
        if (is_string($cartProduct)) {
            return $cartProduct;
        }

        $this->entityManager->remove($cartProduct);
        $this->entityManager->flush();

        return null;
    }

    private function verifyAmountInRange(int $amount, int $stock): string|null
    {
        if ($amount < 1) {
            return 'Amount must be greater than 0';
        } elseif ($amount > $stock) {
            return 'Amount must be less than stock';
        }
        return null;
    }

}