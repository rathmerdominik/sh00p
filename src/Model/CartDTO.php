<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
class CartDTO
{
    public function __construct(
        #[Assert\NotBlank(allowNull: true)]
        public string $name
    ) {
    }
}
