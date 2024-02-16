<?php

namespace App\Tests\Unit\Service;

use App\Entity\Customer;
use App\Entity\ShoppingCart;
use App\Model\CartDTO;
use App\Repository\ShoppingCartRepository;
use App\Service\CustomerService;
use App\Service\ShoppingCartService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ShoppingCartService::class)]
#[UsesClass(ShoppingCart::class)]
#[UsesClass(CartDTO::class)]
class ShoppingCartServiceTest extends KernelTestCase
{
    public MockObject|ShoppingCartRepository|null $shoppingCartRepositoryMock ;
    public MockObject|EntityManagerInterface|null $entityManagerMock;
    public MockObject|CustomerService|null $customerServiceMock;

    public function setUp(): void
    {
        $this->shoppingCartRepositoryMock = $this->createMock(ShoppingCartRepository::class);

        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->entityManagerMock->method('getRepository')
            ->with(ShoppingCart::class)
            ->willReturn($this->shoppingCartRepositoryMock);

        $this->customerServiceMock = $this->createMock(CustomerService::class);
    }

    public function tearDown(): void
    {
        $this->shoppingCartRepositoryMock = null;
        $this->entityManagerMock = null;
        parent::tearDown();
    }
    public function testGetShoppingCartsIsTwoShoppingCarts(): void
    {
        $customerMock = $this->createMock(Customer::class);
        $customerMock
            ->expects($this->once())
            ->method('getShoppingCarts')
            ->willReturn(new ArrayCollection([
                [
                    'id' => 1,
                    'name' => 'TestCart 1',
                ],
                [
                    'id' => 2,
                    'name' => 'TestCart 2',
                ]
            ]));

        $this->customerServiceMock->method('getCustomerById')
            ->with(1)
            ->willReturn($customerMock);

        $shoppingCartService = new ShoppingCartService(
            $this->entityManagerMock,
            $this->shoppingCartRepositoryMock,
            $this->customerServiceMock
        );
        $shoppingCarts = $shoppingCartService->getShoppingCarts(1);
        self::assertCount(2, $shoppingCarts);
    }

    public function testGetShoppingCartsWithInvalidUser(): void
    {
        $this->customerServiceMock->method('getCustomerById')
            ->with(1)
            ->willReturn('Customer not found');

        $shoppingCartService = new ShoppingCartService(
            $this->entityManagerMock,
            $this->shoppingCartRepositoryMock,
            $this->customerServiceMock
        );
        $shoppingCarts = $shoppingCartService->getShoppingCarts(1);
        self::assertEquals('Customer not found', $shoppingCarts);
    }

    public function testGetShoppingCartByIdReturnsThatSpecificCart(): void
    {
        $customerMock = $this->createMock(Customer::class);
        $customerMock->method('getId')
            ->willReturn(1);

        $shoppingCart = new ShoppingCart();
        $shoppingCart->setName("TestCart 1");
        $shoppingCart->setCustomer($customerMock);

        $this->shoppingCartRepositoryMock->method('find')
            ->with(1)
            ->willReturn($shoppingCart);

        $shoppingCartService = new ShoppingCartService(
            $this->entityManagerMock,
            $this->shoppingCartRepositoryMock,
            $this->customerServiceMock
        );
        $shoppingCart = $shoppingCartService->getShoppingCartById(1, 1);

        self::assertSame('TestCart 1', $shoppingCart->getName());
    }

    public function testGetNonExistentShoppingCartByIdReturnsNotFound(): void
    {

        $this->shoppingCartRepositoryMock->method('find')
            ->with(1)
            ->willReturn(null);

        $shoppingCartService = new ShoppingCartService(
            $this->entityManagerMock,
            $this->shoppingCartRepositoryMock,
            $this->customerServiceMock
        );
        $shoppingCart = $shoppingCartService->getShoppingCartById(1, 1);

        self::assertSame('Cart not found', $shoppingCart);
    }

    public function testCreateShoppingCart(): void
    {
        $customer = new Customer();
        $customer->setName("TestCustomer 1");

        $this->customerServiceMock->method('getCustomerById')
            ->with(1)
            ->willReturn($customer);

        $shoppingCartController = new ShoppingCartService(
            $this->entityManagerMock,
            $this->shoppingCartRepositoryMock,
            $this->customerServiceMock
        );
        $shoppingCart = $shoppingCartController->createShoppingCart(
            1,
            new CartDTO("TestCart 1"),
        );

        self::assertSame('TestCart 1', $shoppingCart->getName());
    }

    public function testCreateShoppingCartWithInvalidUser(): void {
        $this->customerServiceMock->method('getCustomerById')
            ->with(1)
            ->willReturn('Customer not found');

        $shoppingCartController = new ShoppingCartService(
            $this->entityManagerMock,
            $this->shoppingCartRepositoryMock,
            $this->customerServiceMock
        );
        $shoppingCart = $shoppingCartController->createShoppingCart(
            1,
            new CartDTO("TestCart 1"),
        );

        self::assertEquals('Customer not found', $shoppingCart);
    }
    public function testEditValidShoppingCart(): void
    {
        $customerMock = $this->createMock(Customer::class);
        $customerMock->method('getId')
            ->willReturn(1);

        $shoppingCart = new ShoppingCart();
        $shoppingCart->setName("TestCart 1");
        $shoppingCart->setCustomer($customerMock);

        $this->shoppingCartRepositoryMock->method('find')
            ->with(1)
            ->willReturn($shoppingCart);

        $shoppingCartController = new ShoppingCartService(
            $this->entityManagerMock,
            $this->shoppingCartRepositoryMock,
            $this->customerServiceMock
        );

        $shoppingCart = $shoppingCartController->editShoppingCart(
            1,
            1,
            new CartDTO("TestCart 2"),
        );

        self::assertSame('TestCart 2', $shoppingCart->getName());
    }

    public function testEditInvalidShoppingCart(): void
    {
        $this->shoppingCartRepositoryMock->method('find')
            ->with(1)
            ->willReturn(null);

        $shoppingCartController = new ShoppingCartService(
            $this->entityManagerMock,
            $this->shoppingCartRepositoryMock,
            $this->customerServiceMock
        );

        $shoppingCart = $shoppingCartController->editShoppingCart(
            1,
            1,
            new CartDTO("TestCart 2"),
        );
        self::assertEquals('Cart not found', $shoppingCart);
    }

    public function testDeleteShoppingCartWithValidId(): void
    {
        $customerMock = $this->createMock(Customer::class);
        $customerMock->method('getId')
            ->willReturn(1);

        $shoppingCart = new ShoppingCart();
        $shoppingCart->setName("TestCart 1");
        $shoppingCart->setCustomer($customerMock);

        $this->shoppingCartRepositoryMock->method('find')
            ->with(1)
            ->willReturn($shoppingCart);

        $shoppingCartService = new ShoppingCartService(
            $this->entityManagerMock,
            $this->shoppingCartRepositoryMock,
            $this->customerServiceMock
        );
        $response = $shoppingCartService->deleteShoppingCart(
            1,
            1,
        );

        self::assertNull($response);
    }

    public function testDeleteShoppingCartWithInvalidId(): void
    {
        $shoppingCartRepositoryMock = $this->createMock(ShoppingCartRepository::class);
        $shoppingCartRepositoryMock->method('find')
            ->with(1)
            ->willReturn(null);

        $shoppingCartController = new ShoppingCartService(
            $this->entityManagerMock,
            $shoppingCartRepositoryMock,
            $this->customerServiceMock
        );
        $response = $shoppingCartController->deleteShoppingCart(
            1,
            1
        );
        self::assertEquals('Cart not found', $response);
    }
}
