<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */

namespace MSBios\Json\Exception;

/**
 * Class InvalidArgumentException
 * @package MSBios\Json\Exception
 */
class InvalidArgumentException extends \MSBios\Exception\InvalidArgumentException
{
    /**
     * @return \Traversable
     */
    public static function invalidDataTypeInJsonStore($data): \Traversable
    {
        return self::create(
            sprintf('Invalid data type in JsonStore. Expected object, array or string, got %s', $data)
        );
    }
}