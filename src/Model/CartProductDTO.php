<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
class CartProductDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public int $amount,

        #[Assert\NotBlank]
        public int $product_id,
    ) {
    }
}