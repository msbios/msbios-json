<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */

namespace MSBios\Json;

use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use Laminas\Json\Json;
use Laminas\Stdlib\ArrayObject;
use MSBios\Json\Exception\InvalidArgumentException;

/**
 * Class Store
 * @package MSBios\Json
 * @TODO: доделать работу в паре $this => ArrayObject
 */
class Store extends ArrayObject
{
    /** @var array */
    private static $emptyArray = [];

    /** @var array */
    private $data;

    /** @var Path */
    private $path;

    /**
     * Store constructor.
     * @param array $data
     */
    public function __construct($data)
    {
        $this->path = new Path;
        $this->setData($data);
        parent::__construct($this->data);
    }

    /**
     * Sets JsonStore's manipulated data
     * @param string|array|\stdClass $data
     */
    public function setData($data)
    {
        $this->data = $data;

        if (is_string($this->data)) {
            $this->data = Decoder::decode($this->data, Json::TYPE_ARRAY);
        } elseif (is_object($data)) {
            $this->data = Decoder::decode(Json::encode($this->data), Json::TYPE_ARRAY);
        } elseif (! is_array($data)) {
            throw InvalidArgumentException::invalidDataTypeInJsonStore(gettype($data));
        }
    }

    /**
     * JsonEncoded version of the object
     *
     * @return string
     */
    public function toString()
    {
        return Encoder::encode($this->data);
    }

    /**
     * Returns the given json string to object
     * @return \stdClass
     */
    public function toObject()
    {
        return Decoder::decode(Encoder::encode($this->data));
    }

    /**
     * Returns the given json string to array
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Find elements matching the given Path expression
     *
     * @param string $expr JsonPath expression
     * @param bool $unique Gets unique results or not
     * @return array
     * @throws \Exception
     */
    public function find($expr, $unique = false)
    {

        if (! (false !== ($exprs = $this->normalizedFirst($expr))
            && (is_array($exprs) || $exprs instanceof \Traversable))) {
            return self::$emptyArray;
        };


        /** @var array $values */
        $values = [];

        /** @var string $expr */
        foreach ($exprs as $expr) {
            $o =& $this->data;
            $keys = preg_split(
                "/([\"'])?\]\[([\"'])?/",
                preg_replace(["/^\\$\[[\"']?/", "/[\"']?\]$/"], "", $expr)
            );
            for ($i = 0; $i < count($keys); $i++) {
                $o =& $o[$keys[$i]];
            }

            $values[] = &$o;
        }

        if (true === $unique) {
            if (! empty($values) && is_array($values[0])) {
                array_walk($values, function (&$value) {
                    $value = Encoder::encode($value);
                });

                $values = array_unique($values);

                array_walk($values, function (&$value) {
                    $value = Decoder::decode($value, Json::TYPE_ARRAY);
                });

                return array_values($values);
            }

            return array_unique($values);
        }

        // if (1 == count($values)) {
        //     $values = $values[0];
        // }

        return $values;
    }

    /**
     * Sets the value for all elements matching the given JsonPath expression
     * @param string $expr JsonPath expression
     * @param mixed $value Value to set
     * @return bool returns true if success
     * @throws \Exception
     */
    public function set($expr, $value)
    {
        $get = $this->find($expr);
        if ($res =& $get) {
            foreach ($res as &$r) {
                $r = $value;
            }
            return true;
        }
        return false;
    }

    /**
     * Adds one or more elements matching the given json path expression
     * @param string $parentexpr JsonPath expression to the parent
     * @param mixed $value Value to add
     * @param string $name Key name
     * @return bool returns true if success
     * @throws \Exception
     */
    public function add($parentexpr, $value, $name = "")
    {
        $get = $this->find($parentexpr);
        if ($parents =& $get) {
            foreach ($parents as &$parent) {
                $parent = is_array($parent) ? $parent : [];
                if ($name != "") {
                    $parent[$name] = $value;
                } else {
                    $parent[] = $value;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Removes all elements matching the given jsonpath expression
     * @param string $expr JsonPath expression
     * @return bool returns true if success
     * @throws \Exception
     */
    public function remove($expr)
    {
        if ((($exprs = $this->normalizedFirst($expr)) !== false) &&
            (is_array($exprs) || $exprs instanceof \Traversable)
        ) {
            foreach ($exprs as &$expr) {
                $o =& $this->data;
                $keys = preg_split(
                    "/([\"'])?\]\[([\"'])?/",
                    preg_replace(["/^\\$\[[\"']?/", "/[\"']?\]$/"], "", $expr)
                );
                for ($i = 0; $i < count($keys) - 1; $i++) {
                    $o =& $o[$keys[$i]];
                }
                unset($o[$keys[$i]]);
            }
            return true;
        }
        return false;
    }

    /**
     * @param $expr
     * @return array|bool
     * @throws \Exception
     */
    private function normalizedFirst($expr)
    {
        if ($expr == "") {
            return false;
        } else {
            if (preg_match("/^\$(\[([0-9*]+|'[-a-zA-Z0-9_ ]+')\])*$/", $expr)) {
                print("normalized: " . $expr);
                return $expr;
            } else {
                $res = $this->path->find($this->data, $expr, ["resultType" => "PATH"]);
                return $res;
            }
        }
    }
}
