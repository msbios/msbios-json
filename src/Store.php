<?php
/**
 * Created by PhpStorm.
 * User: judzhin
 * Date: 8/14/17
 * Time: 5:47 PM
 */

namespace MSBios\Json;

use Zend\Json\Json;

/**
 * Class Store
 * @package MSBios\Json
 */
class Store
{
    /** @var array  */
    private static $emptyArray = [];

    /** @var array */
    private $data;

    /** @var Path */
    private $path;

    /**
     * Store constructor.
     * @param string|array|\stdClass $data
     */
    public function __construct($data)
    {
        $this->path = new Path;
        $this->setData($data);
    }

    /**
     * Sets JsonStore's manipulated data
     * @param string|array|\stdClass $data
     */
    public function setData($data)
    {
        $this->data = $data;
        if (is_string($this->data)) {
            $this->data = Json::decode($this->data, Json::TYPE_ARRAY);
        } else if (is_object($data)) {
            $this->data = Json::decode(Json::encode($this->data), Json::TYPE_ARRAY);
        } else if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf('Invalid data type in JsonStore. Expected object, array or string, got %s', gettype($data)));
        }

    }

    /**
     * JsonEncoded version of the object
     * @return string
     */
    public function toString()
    {
        return Json::encode($this->data);
    }

    /**
     * Returns the given json string to object
     * @return \stdClass
     */
    public function toObject()
    {
        return Json::decode(Json::encode($this->data));
    }

    /**
     * Returns the given json string to array
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Find elements matching the given Path expression
     *
     * @param string $expr JsonPath expression
     * @param bool $unique Gets unique results or not
     * @return array
     */
    public function find($expr, $unique = false)
    {
        if ((($exprs = $this->normalizedFirst($expr)) !== false)
            && (is_array($exprs) || $exprs instanceof \Traversable)
        ) {
            /** @var array $values */
            $values = [];

            /** @var string $expr */
            foreach ($exprs as $expr) {

                $o =& $this->data;
                $keys = preg_split(
                    "/([\"'])?\]\[([\"'])?/",
                    preg_replace(array("/^\\$\[[\"']?/", "/[\"']?\]$/"), "", $expr)
                );
                for ($i = 0; $i < count($keys); $i++) {
                    $o =& $o[$keys[$i]];
                }

                $values[] = & $o;
            }

            if (true === $unique) {

                if (!empty($values) && is_array($values[0])) {

                    array_walk($values, function(&$value) {
                        $value = json_encode($value);
                    });

                    $values = array_unique($values);

                    array_walk($values, function(&$value) {
                        $value = json_decode($value, true);
                    });

                    return array_values($values);
                }

                return array_unique($values);
            }

            if (1 == count($values)){
                $values = $values[0];
            }

            return $values;
        }

        return self::$emptyArray;
    }

    /**
     * Sets the value for all elements matching the given JsonPath expression
     * @param string $expr JsonPath expression
     * @param mixed $value Value to set
     * @return bool returns true if success
     */
    function set($expr, $value)
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
     */
    public function add($parentexpr, $value, $name = "")
    {
        $get = $this->find($parentexpr);
        if ($parents =& $get) {
            foreach ($parents as &$parent) {
                $parent = is_array($parent) ? $parent : array();
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
                    preg_replace(array("/^\\$\[[\"']?/", "/[\"']?\]$/"), "", $expr)
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