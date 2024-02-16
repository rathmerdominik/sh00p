<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Customer;
use App\Model\CartProductDTO;
use App\Model\CustomerDTO;
use App\Repository\CustomerRepository;
use App\Service\CustomerService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(CustomerService::class)]
#[UsesClass(Customer::class)]
#[UsesClass(CartProductDTO::class)]
class CustomerServiceTest extends KernelTestCase
{
    public MockObject|CustomerRepository|null $customerRepositoryMock ;
    public MockObject|EntityManagerInterface|null $entityManagerMock;

    public function setUp(): void
    {
        $this->customerRepositoryMock = $this->createMock(CustomerRepository::class);

        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->entityManagerMock->method('getRepository')
            ->with(Customer::class)
            ->willReturn($this->customerRepositoryMock);

    }

    public function tearDown(): void
    {
        $this->customerRepositoryMock = null;
        $this->entityManagerMock = null;
        parent::tearDown();
    }

    public function testGetCustomersIsTwoCustomers(): void
    {
        $this->customerRepositoryMock->expects($this->once())
            ->method('findAll')
            ->willReturn([
                [
                    'id' => 1,
                    'name' => 'TestUser 1',
                ],
                [
                    'id' => 2,
                    'name' => 'TestUser 2',
                ]
            ]);
        $customerService = new CustomerService($this->entityManagerMock, $this->customerRepositoryMock);
        $customers = $customerService->getCustomers();

        self::assertEquals('TestUser 1', $customers[0]['name']);
        self::assertEquals('TestUser 2', $customers[1]['name']);
    }

    public function testGetCustomerByIdReturnsThatSpecificCustomer(): void
    {
        $customer = new Customer();
        $customer->setName("TestUser 1");

        $this->customerRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($customer);

        $customerService = new CustomerService($this->entityManagerMock, $this->customerRepositoryMock);
        $customer = $customerService->getCustomerById(1);

        self::assertEquals('TestUser 1', $customer->getName());
    }

    public function testGetNotExistentCustomerByIdReturnsNotFound(): void
    {
        $this->customerRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $customerService = new CustomerService($this->entityManagerMock, $this->customerRepositoryMock);
        $customer = $customerService->getCustomerById(1);

        self::assertEquals('Customer not found', $customer);
    }

    public function testCreateCustomer(): void
    {
        $customerService = new CustomerService($this->entityManagerMock, $this->customerRepositoryMock);
        $customerDTO = new CustomerDTO("TestUser 1");

        $customer = $customerService->createCustomer(
            $customerDTO
        );

        self::assertSame('TestUser 1', $customer->getName());
    }

    public function testEditValidCustomer(): void
    {
        $customer = new Customer();
        $customer->setName("TestUser 1");
        $this->customerRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($customer);

        $customerService = new CustomerService($this->entityManagerMock, $this->customerRepositoryMock);

        $customer = $customerService->editCustomer(
            1,
            new CustomerDTO("TestUser 2")
        );

        self::assertSame('TestUser 2', $customer->getName());
    }

    public function testEditInvalidCustomer(): void
    {
        $this->customerRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $customerService = new CustomerService($this->entityManagerMock, $this->customerRepositoryMock);
        $customer = $customerService->editCustomer(
            1,
            new CustomerDTO("TestUser 2")
        );

        self::assertEquals('Customer not found', $customer);
    }

    public function testDeleteCustomerWithValidId(): void
    {
        $customer = new Customer();
        $customer->setName("TestUser 1");

        $this->customerRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn($customer);

        $customerService = new CustomerService($this->entityManagerMock, $this->customerRepositoryMock);
        $response = $customerService->deleteCustomer(
            1
        );

        self::assertNull($response);
    }

    public function testDeleteCustomerWithInvalidId(): void
    {
        $this->customerRepositoryMock
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $customerService = new CustomerService($this->entityManagerMock, $this->customerRepositoryMock);
        $response = $customerService->deleteCustomer(
            1
        );

        self::assertEquals('Customer not found', $response);
    }
}
