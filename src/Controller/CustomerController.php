<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\CustomerDTO;
use App\Service\CustomerService;
use App\Service\EntitySerializerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/api/v1/customers')]
class CustomerController extends AbstractController
{
    public function __construct(
        public CustomerService $customerService,
        public EntitySerializerService $entitySerializerService
    ) {}

    #[Route('', name: 'app_customers_get', methods: ['GET'], format: 'json')]
    public function getCustomers(): JsonResponse
    {
        $customers = $this->customerService->getCustomers();
        return new JsonResponse($this->entitySerializerService->serializeEntity($customers), Response::HTTP_OK, json: true);
    }

    #[Route('/{customer_id}', name: 'app_customers_get_by_id', methods: ['GET'], format: 'json')]
    public function getCustomerById(int $customer_id): JsonResponse
    {
        $customer = $this->customerService->getCustomerById($customer_id);
        if (is_string($customer)) {
            return new JsonResponse(['error' => $customer], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($this->entitySerializerService->serializeEntity($customer), Response::HTTP_OK, json: true);
    }

    #[Route('', name: 'app_customers_create', methods: ['POST'], format: 'json')]
    public function createCustomer(
        #[MapRequestPayload] CustomerDTO $customerDto
    ): JsonResponse
    {
        $customer = $this->customerService->createCustomer($customerDto);
        return new JsonResponse($this->entitySerializerService->serializeEntity($customer), Response::HTTP_CREATED, json: true);
    }

    #[Route('/{customer_id}', name: 'app_customers_edit', methods: ['PATCH'], format: 'json')]
    public function editCustomer(
        #[MapRequestPayload] CustomerDTO $customerDto,
        int $customer_id
    ): JsonResponse {
        $customer = $this->customerService->editCustomer($customer_id, $customerDto);
        if (is_string($customer)) {
            return new JsonResponse(['error' => $customer], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($this->entitySerializerService->serializeEntity($customer), Response::HTTP_OK, json: true);
    }

    #[Route('/{customer_id}', name: 'app_customers_delete', methods: ['DELETE'], format: 'json')]
    public function deleteCustomer(
        int $customer_id
    ): JsonResponse
    {
        $customer = $this->customerService->deleteCustomer($customer_id);
        if (is_string($customer)) {
            return new JsonResponse(['error' => $customer], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse('', Response::HTTP_NO_CONTENT, json:true);
    }
}
