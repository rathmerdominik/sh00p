<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Customer;
use App\Model\CustomerDTO;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;

class CustomerService
{
    public function __construct(
        public EntityManagerInterface $entityManager,
        public CustomerRepository $customerRepository
    ){
    }

    /**
     * @return Customer[]
     */
    public function getCustomers(): array
    {
        return $this->customerRepository->findAll();
    }

    public function getCustomerById(int $customer_id): Customer|string
    {
        $customer = $this->customerRepository->find($customer_id);
        if (!$customer) {
            return ErrorMessage::CUSTOMER_NOT_FOUND;
        }
        return $customer;
    }

    public function createCustomer(CustomerDTO $customerDTO): Customer
    {
        $customer = new Customer();
        $customer->setName($customerDTO->name);

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return $customer;
    }

    public function editCustomer(
        int $customer_id, CustomerDTO $customerDTO
    ): Customer|string
    {
        $customer = $this->entityManager->getRepository(Customer::class)->find($customer_id);

        if(!$customer) {
            return ErrorMessage::CUSTOMER_NOT_FOUND;
        }

        $customer->setName($customerDTO->name);
        $this->entityManager->flush();
        return $customer;
    }

    public function deleteCustomer(int $customer_id): null|string
    {
        $customer = $this->entityManager->getRepository(Customer::class)->find($customer_id);

        if (!$customer) {
            return ErrorMessage::CUSTOMER_NOT_FOUND;
        }

        $this->entityManager->remove($customer);
        $this->entityManager->flush();

        return null;
    }
}