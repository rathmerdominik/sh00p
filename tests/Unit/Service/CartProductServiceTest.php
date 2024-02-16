<?php

namespace App\Tests\Unit\Service;

use App\Entity\CartProduct;
use App\Entity\Product;
use App\Entity\ShoppingCart;
use App\Model\CartProductDTO;
use App\Repository\ProductRepository;
use App\Repository\ShoppingCartRepository;
use App\Service\CartProductService;
use App\Service\ShoppingCartService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(CartProductService::class)]
#[UsesClass(CartProductDTO::class)]
#[UsesClass(CartProduct::class)]
class CartProductServiceTest extends KernelTestCase
{
    public MockObject|ProductRepository|null $productRepositoryMock;
    public MockObject|EntityManagerInterface|null $entityManagerMock;
    public MockObject|ShoppingCartService|null $shoppingCartServiceMock;

    public function setUp(): void
    {
        $this->productRepositoryMock = $this->createMock(ProductRepository::class);
        $shoppingCartRepositoryMock = $this->createMock(ShoppingCartRepository::class);

        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->entityManagerMock->method('getRepository')
            ->with(ShoppingCart::class)
            ->willReturn($shoppingCartRepositoryMock);

        $this->shoppingCartServiceMock = $this->createMock(ShoppingCartService::class);
    }

    public function tearDown(): void
    {
        $this->shoppingCartServiceMock = null;
        $this->entityManagerMock = null;
        $this->productRepositoryMock = null;
        parent::tearDown();
    }

    public function testGetCartProductsIsTwoCartProducts(): void
    {
        $shoppingCart = new ShoppingCart();
        $shoppingCart->addCartProduct(new CartProduct());
        $shoppingCart->addCartProduct(new CartProduct());

        $this->shoppingCartServiceMock->expects($this->once())
            ->method('getShoppingCartById')
            ->willReturn($shoppingCart);

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProducts = $cartProductService->getCartProducts(1, 1);
        self::assertCount(2, $cartProducts);
    }

    public function testGetCartProductsWithInvalidShoppingCart(): void
    {
        $this->shoppingCartServiceMock->expects($this->once())
            ->method('getShoppingCartById')
            ->willReturn('Shopping cart not found');

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProducts = $cartProductService->getCartProducts(1, 1);
        self::assertCount(0, $cartProducts);
    }

    public function testGetCartProductsByIdIsThatSpecificCartProduct(): void
    {
        $cartProductMock = $this->createMock(CartProduct::class);
        $cartProductMock->method('getId')
            ->willReturn(1);

        $shoppingCart = new ShoppingCart();
        $shoppingCart->addCartProduct($cartProductMock);

        $this->shoppingCartServiceMock->expects($this->once())
            ->method('getShoppingCartById')
            ->willReturn($shoppingCart);

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProduct = $cartProductService->getCartProductById(1, 1, 1);
        self::assertEquals(1, $cartProduct->getId());
    }

    public function testGetCartProductByIdWithInvalidShoppingCartReturnsNotFound(): void
    {
        $this->shoppingCartServiceMock->expects($this->once())
            ->method('getShoppingCartById')
            ->willReturn('Shopping cart not found');

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProduct = $cartProductService->getCartProductById(1, 1, 1);
        self::assertEquals('Shopping cart not found', $cartProduct);
    }

    public function testGetCartProductByIdWithInvalidProductInCart(): void
    {
        $cartProductMock = $this->createMock(CartProduct::class);
        $cartProductMock->method('getId')
            ->willReturn(2);

        $shoppingCart = new ShoppingCart();
        $shoppingCart->addCartProduct($cartProductMock);

        $this->shoppingCartServiceMock->expects($this->once())
            ->method('getShoppingCartById')
            ->willReturn($shoppingCart);

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProduct = $cartProductService->getCartProductById(1, 1, 1);
        self::assertEquals('Product in cart not found', $cartProduct);
    }

    public function testAddCartProductToShoppingCart(): void
    {
        $cartProductDTO = new CartProductDTO(1, 1);

        $productMock = $this->createMock(Product::class);
        $productMock->method('getStock')
            ->willReturn(10);

        $this->productRepositoryMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($productMock);

        $shoppingCart = new ShoppingCart();
        $this->shoppingCartServiceMock->expects($this->once())
            ->method('getShoppingCartById')
            ->willReturn($shoppingCart);

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProduct = $cartProductService->addCartProductToCart(1, 1, $cartProductDTO);
        self::assertInstanceOf(CartProduct::class, $cartProduct);
    }

    public function testAddCartProductToShoppingCartWithInvalidProduct(): void
    {
        $cartProductDTO = new CartProductDTO(1, 1);

        $this->productRepositoryMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $shoppingCart = new ShoppingCart();
        $this->shoppingCartServiceMock->expects($this->once())
            ->method('getShoppingCartById')
            ->willReturn($shoppingCart);

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProduct = $cartProductService->addCartProductToCart(1, 1, $cartProductDTO);
        self::assertEquals('Product not found', $cartProduct);
    }

    public function testAddCartProductToShoppingCartWithAmountLargerThanStock(): void
    {
        $cartProductDTO = new CartProductDTO(11, 1);

        $productMock = $this->createMock(Product::class);
        $productMock->method('getStock')
            ->willReturn(10);

        $this->productRepositoryMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($productMock);

        $shoppingCart = new ShoppingCart();
        $this->shoppingCartServiceMock->expects($this->once())
            ->method('getShoppingCartById')
            ->willReturn($shoppingCart);

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProduct = $cartProductService->addCartProductToCart(1, 1, $cartProductDTO);
        self::assertEquals('Amount must be less than stock', $cartProduct);
    }
    public function testAddCartProductToShoppingCartWithAmountLessThanZero(): void
    {
        $cartProductDTO = new CartProductDTO(-1, 1);

        $productMock = $this->createMock(Product::class);
        $productMock->method('getStock')
            ->willReturn(10);

        $this->productRepositoryMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($productMock);

        $shoppingCart = new ShoppingCart();
        $this->shoppingCartServiceMock->expects($this->once())
            ->method('getShoppingCartById')
            ->willReturn($shoppingCart);

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProduct = $cartProductService->addCartProductToCart(1, 1, $cartProductDTO);
        self::assertEquals('Amount must be greater than 0', $cartProduct);
    }

    public function testAddCartProductToShoppingCartWithInvalidShoppingCart(): void
    {
        $cartProductDTO = new CartProductDTO(1, 1);

        $this->productRepositoryMock->expects($this->never())
            ->method('find');

        $this->shoppingCartServiceMock->expects($this->once())
            ->method('getShoppingCartById')
            ->willReturn('Shopping cart not found');

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProduct = $cartProductService->addCartProductToCart(1, 1, $cartProductDTO);
        self::assertEquals('Shopping cart not found', $cartProduct);
    }

    public function testEditCartProduct(): void
    {
        $cartProductDTO = new CartProductDTO(5, 1);

        $productMock = $this->createMock(Product::class);
        $productMock->method('getStock')
            ->willReturn(10);

        $cartProduct = $this->createMock(CartProduct::class);
        $cartProduct->method('getId')
            ->willReturn(1);
        $cartProduct->method('getProduct')
            ->willReturn($productMock);
        $cartProduct->setAmount(2);
        $cartProduct->setProduct($productMock);

        $shoppingCart = new ShoppingCart();
        $shoppingCart->addCartProduct($cartProduct);

        $this->shoppingCartServiceMock->method('getShoppingCartById')
            ->willReturn($shoppingCart);

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProduct = $cartProductService->editCartProduct(1, 1, 1, $cartProductDTO);

        self::assertInstanceOf(CartProduct::class, $cartProduct);
    }

    public function testEditCartProductWithInvalidCartProduct(): void
    {
        $cartProductDTO = new CartProductDTO(5, 1);

        $this->shoppingCartServiceMock->method('getShoppingCartById')
            ->willReturn('Shopping cart not found');

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProduct = $cartProductService->editCartProduct(1, 1, 1, $cartProductDTO);

        self::assertEquals('Shopping cart not found', $cartProduct);
    }

    public function testEditCartProductWithAmountLargerThanStock(): void
    {
        $cartProductDTO = new CartProductDTO(11, 1);

        $productMock = $this->createMock(Product::class);
        $productMock->method('getStock')
            ->willReturn(10);

        $cartProduct = $this->createMock(CartProduct::class);
        $cartProduct->method('getId')
            ->willReturn(1);
        $cartProduct->method('getProduct')
            ->willReturn($productMock);
        $cartProduct->setAmount(2);
        $cartProduct->setProduct($productMock);

        $shoppingCart = new ShoppingCart();
        $shoppingCart->addCartProduct($cartProduct);

        $this->shoppingCartServiceMock->method('getShoppingCartById')
            ->willReturn($shoppingCart);

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProduct = $cartProductService->editCartProduct(1, 1, 1, $cartProductDTO);

        self::assertEquals('Amount must be less than stock', $cartProduct);
    }

    public function testDeleteCartProduct(): void
    {
        $cartProduct = $this->createMock(CartProduct::class);
        $cartProduct->method('getId')
            ->willReturn(1);

        $shoppingCart = new ShoppingCart();
        $shoppingCart->addCartProduct($cartProduct);

        $this->shoppingCartServiceMock->method('getShoppingCartById')
            ->willReturn($shoppingCart);

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProduct = $cartProductService->deleteCartProduct(1, 1, 1);

        self::assertNull($cartProduct);
    }

    public function testDeleteCartProductWithInvalidCartProduct(): void
    {
        $this->shoppingCartServiceMock->method('getShoppingCartById')
            ->willReturn('Shopping cart not found');

        $cartProductService = new CartProductService(
            $this->entityManagerMock,
            $this->shoppingCartServiceMock,
            $this->productRepositoryMock
        );
        $cartProduct = $cartProductService->deleteCartProduct(1, 1, 1);

        self::assertEquals('Shopping cart not found', $cartProduct);
    }
}