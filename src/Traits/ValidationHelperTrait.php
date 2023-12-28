<?php declare(strict_types=1);

namespace Conflabs\JsonFileViewer\Traits;

use Exception;

trait ValidationHelperTrait
{

    /**
     * @param string $buffer
     * @return array
     */
    public static function validateProductNameLengths(string $buffer): array
    {

        return array_values(array_filter(array_map(function ($invTxfrItem) {
            if (strlen($invTxfrItem['product_name']) > 300) {
                return $invTxfrItem['product_name'];
            }
            return null;
        }, json_decode($buffer, true)['inventory_transfer_items'])));
    }

    /**
     * @param string $buffer
     * @return array
     */
    public static function validateProductQuantities(string $buffer): array
    {

        return array_values(array_filter(array_map(function ($invTxfrItem) {
            if ((float)$invTxfrItem['qty'] == 0) {
                return (float)$invTxfrItem['qty'];
            }
            return null;
        }, json_decode($buffer, true)['inventory_transfer_items'])));
    }

    /**
     * @param string $buffer
     * @return array|string|null
     */
    public static function validateEmptyValues(string $buffer): array|string|null
    {
        return preg_replace('/"value":\s+}/', '"value": 0}', $buffer);
    }

    /**
     * @param string $buffer
     * @return array|string|null
     */
    public static function validateNullStringsToValues(string $buffer): array|string|null
    {
        return str_replace('"null"', 'null', $buffer);
    }
}