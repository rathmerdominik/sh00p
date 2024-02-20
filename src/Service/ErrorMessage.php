<?php
declare(strict_types=1);

namespace App\Service;

/**
 * @codeCoverageIgnore
 */
class ErrorMessage
{
    public const CUSTOMER_NOT_FOUND = 'Customer not found';
    public const CART_NOT_FOUND = 'Cart not found';
    public const PRODUCT_NOT_FOUND = 'Product not found';
    public const PRODUCT_IN_CART_NOT_FOUND = 'Product in cart not found';
    public const AMOUNT_EQUAL_OR_LOWER_THAN_ZERO = 'Amount must be greater than 0';
    public const AMOUNT_GREATER_THAN_STOCK = 'Amount must be less than stock';
}
