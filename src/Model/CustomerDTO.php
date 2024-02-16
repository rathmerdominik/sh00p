<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
class CustomerDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,
    ) {
    }
}
