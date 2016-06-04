<?php
/* 
 * (c) 2016 by Dennis Birkholz <dennis@birkholz.biz>
 * All rights reserved.
 * For the license to use this code, see the bundled LICENSE file.
 */
namespace dennisbirkholz\ber\type;

use dennisbirkholz\ber\Parser;
use dennisbirkholz\ber\Type;

class Sequence extends Type implements \ArrayAccess
{
    const TYPE	= Type::T_CONSTRUCTED;
    const CLS	= Type::C_UNIVERSAL;
    const TAG	= 16;
    
    /**
     * Constraints for the Sequence-Type work like the following:
     * 1. Constraints are a list of key-value-pairs
     * 2. Key is the variable name to parse the element to
     * 3.a) Value is a class name to parse the element as
     * 3.b) Value is an array containing information how to process the element (TODO)
     *  - First element must be the class name to use OR the special keyword 'CHOICE'
     *  - The element with key 'optional' can be TRUE or FALSE to indicate the element does not need to appear
     *  - If the first element is CHOICE, a value with key 'options' and an array containing all allowed class names must be present
     * 4. The order of the elements is important. The data must appear in this order
     */
    protected static $definition = array();
    
    protected static $randomOrder = false;
    
    public $value = null;
    
    public function __construct(array $value) {
        $this->value = $value;
    }
    
    /**
     * {@inheritdoc}
     */
    public static function parse(Parser $parser, $data)
    {
        // Fallback to deprecated behaviour: use global registry to determine types
        if (count(static::$definition) === 0) {
            return new static($parser->parse($data));
        }
        
        $elements = Parser::parseToArray($data);
        if (count($elements) > count(static::$definition)) {
            throw new \Exception('More elements found for this sequence, got #' . count($elements) . ', only #' . count(static::$definition) . ' allowed.');
        }
        
        foreach (static::$definition AS $var => $classes) {
            $optional = false;
            $default = null;
            
            if (is_array($classes)) {
                if (isset($classes['optional'])) {
                    $optional = $classes['optional'];
                    unset($classes['optional']);
                }
                
                if (isset($classes['default'])) {
                    $default = $classes['default'];
                    $optional = true;
                    unset($classes['default']);
                }
            } else {
                $classes = array($classes);
            }
            
            $match = false;
            
            foreach ($elements AS $num => $element) {
                // print 'Found element with (' . $element['type'] . ', ' . $element['class'] . ', ' . $element['tag'] . ')' . "\n";
                
                foreach ($classes AS $class) {
                    // print 'Comparing to (' . $class::TYPE . ', ' . $class::CLS . ', ' . $class::TAG . ')' . "\n";
                    
                    if ($class::TYPE !== $element['type']) { continue; }
                    if ($class::CLS  !== $element['class']) { continue; }
                    if ($class::TAG  !== $element['tag']) { continue; }
                    
                    $this->$var = new $class();
                    $this->$var->parse($data, $element['pos'], $element['length']);
                    
                    $match = true;
                    unset($elements[$num]);
                    
                    break 2;
                }
                
                // Random order, try to find match in next element
                if (!static::$randomOrder) { break; }
            }
            
            if (!$match && !$optional) {
                throw new \Exception('Found no data for element "' . $var . '" of type "' . (count($classes) > 1 ? 'CHOICE:(' . implode('|', $classes) . ')' : $classes[0] ) . '"');
            }
        }
        
        if (count($elements)) {
            throw new \Exception('Could not find matching elements for all source data.');
        }
    }
    
    public function encodeData()
    {
        $return = '';
        
        foreach (static::$definition AS $var => $class) {
            $optional = false;
            $default = null;
            
            if (is_array($class)) {
                if (isset($class['optional'])) {
                    $optional = $class['optional'];
                    unset($class['optional']);
                }
                
                if (isset($class['default'])) {
                    $default = $class['default'];
                    unset($class['default']);
                }
                
                if (count($class) === 1) {
                    $class = $class[0];
                }
            }
            
            // Elements that are not optional and have no default must be set
            if (!isset($this->$var) || is_null($this->$var)) {
                if (!$optional && is_null($default)) {
                    throw new \Exception('Element "' . $var . '" not set.');
                }
                continue;
            }
            
            // On "no-choice" primitive entries we accept primitive php datatypes
            if ((!is_array($class)) && ($class::TYPE === self::T_PRIMITIVE) && (!$this->$var instanceof $class)) {
                $v = $this->$var;
                $this->$var = new $class();
                $this->$var->init($v);
            }
            
            // Do not encode defaults
            if (!is_null($default) && ($this->$var->$value === $default)) {
                print 'Skipping default value for "' . $var . '".' . "\n";
                continue;
            }
            
            // "choice"-entries must already be one of the allowed datatypes
            if (is_array($class) && ((!$find = get_class($this->$var)) || (!array_search($find, $class))) ) {
                throw new Exception('Element "' . $var . '" has none of the allowed classes.');
            }
            
            $return .= $this->$var->encode();
        }
        
        return $return;
    }
    
    final public function __get($key)
    {
        // Value saved, return
        if (isset($this->$key)) {
            $class = get_class($this->$key);
            
            if (($class::TYPE === self::T_PRIMITIVE) && isset($this->$key->value)) {
                return $this->$key->value;
            } else {
                return $this->$key;
            }
        }
        
        if (is_array(static::$definition[$key]) && isset(static::$definition[$key]['default'])) {
            return static::$definition[$key]['default'];
        }
        
        return null;
    }
    
    final public function __set($key, $value)
    {
        if (is_null($key)) {
            throw new \InvalidArgumentException('Cannot append anonymous data.');
        }
        
        if (!isset(static::$definition[$key])) {
            throw new \InvalidArgumentException('Field "' . $key . '" not allowed in sequence of type "' . get_class($this) . '".');
        }
        
        $classes = static::$definition[$key];
        
        // Simplify definition to match against
        if (is_array($classes)) {
            unset($classes['default'], $classes['optional']);
            if (count($classes) === 1) { $classes = $classes[0]; }
        }
        
        // Handle CHOICE type
        if (is_array($classes)) {
            if (!$value instanceof Type) {
                throw new \InvalidArgumentException('Supplied value for parameter "' . $key . '" must be one of CHOICE:(' . implode('|', $classes) . ').');
            }
            
            foreach ($classes AS $class) {
                if (!$value instanceof $class) { continue; }

                $this->$key = $value;
                return;
            }
            
            throw new \InvalidArgumentException('Supplied value of type "' . get_class($value) . '" for parameter "' . $key . '" is none of CHOICE:(' . implode('|', $classes) . ').');
        }
        
        // Handle default type
        if ($value instanceof $classes) {
            $this->$key = $value;
        } else {
            $this->$key = new $classes();
            $this->$key->init($value);
        }
    }
    
    // Implementing the ArrayAccess interface
    
    final public function offsetGet($key)
    {
        return $this->__get($key);
    }
    
    final public function offsetSet($key, $value)
    {
        return $this->__set($key, $value);
    }
    
    public function offsetExists($key)
    {
        return isset(static::$definition[$key]);
    }
    
    public function offsetUnset($key)
    {
        unset($this->$key);
    }
    
    public function value()
    {
        return $this->value;
    }
}
