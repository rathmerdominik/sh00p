<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'sh00p:generate-products',
    description: 'Generate products for the example',
)]
class GenerateProductsCommand extends Command
{
    public  function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            ->addArgument('amount', InputArgument::REQUIRED, 'The amount of products to generate.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $productAmount = $input->getArgument('amount');
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < $productAmount; $i++) {
            $product = new Product();

            $name = $faker->words(3, true);
            $stock = $faker->randomNumber(2);
            $price = $faker->randomFloat(2);

            $output->writeln([
                'Generating Product',
                '==================',
                'Name: ' . $name,
                'Stock: ' . $stock,
                'Price: ' . $price,
                '==================',
                ''
                ]
            );

            $product->setName($name);
            $product->setStock($stock);
            $product->setPrice($price);
            try {
                $this->entityManager->persist($product);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $output->writeln('Error while generating this product: ' . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}