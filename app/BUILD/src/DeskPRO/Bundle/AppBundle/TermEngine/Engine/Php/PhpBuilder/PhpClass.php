<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder;

class PhpClass
{
    protected $name;
    protected $properties;
    protected $methods;
    protected $implements;
    protected $extends;

    public function __construct()
    {
        $this->properties = [];
        $this->methods    = [];
        $this->implements = [];
    }

    public function __toString()
    {
        $name              = $this->getName();
        $properties        = $this->generatePropertiesString();
        $methods           = $this->generateMethodsString();
        $class_declaration = $this->generateClassDeclaration();

        return <<< PHPCODE
$class_declaration
{
$properties

$methods
}
PHPCODE;
    }

    protected function generateClassDeclaration()
    {
        if (!$this->getName()) {
            // if nothing was declared as the name, we need a name to make the
            // class declaration, so we'll make a unique name.
            $this->setName(uniqid('class'));
        }

        $declaration = 'class ';
        $declaration .= $this->getName();
        if ($extends = $this->getExtends()) {
            $declaration .= ' extends '.$extends;
        }
        if (count($implements = $this->getImplements())) {
            $declaration .= ' implements ';
            $declaration .= implode(', ', $implements);
        }

        return $declaration;
    }

    protected function generateMethodsString()
    {
        $return = [];

        foreach ($this->getMethods() as $method) {
            $return[] = (string) $method;
        }

        return implode("\n\n", $return);
    }

    protected function generatePropertiesString()
    {
        $return = [];
        foreach ($this->properties as $property_info) {
            $string = $property_info['visibility'].' $'.$property_info['name'];
            if ($default = $property_info['default']) {
                $string .= ' = ';
                if (is_int($default) || is_float($default) || in_array(
                        $default,
                        ['null', 'array()', 'true', 'false']
                    )
                ) {
                    $string .= $default;
                } else {
                    $string .= "'$default'"; // make it a string
                }
            }

            $return[] = $string.';';
        }

        return implode("\n", $return);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getProperties()
    {
        return $this->properties;
    }

    public function addProperty($name, $visibility = 'public', $default = null)
    {
        if (null === $visibility) {
            $visibility = 'public';
        }
        $this->properties[$name] = [
            'name'       => $name,
            'visibility' => $visibility,
            'default'    => $default,
        ];
    }

    public function removeProperty($name)
    {
        if (array_key_exists($name, $this->properties)) {
            unset($this->properties[$name]);
        }
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function addMethod(PhpMethod $method)
    {
        $method_name = $method->getName();

        if (!$method_name) {
            $method_name = uniqid('func');
            $method->setName($method_name);
        }

        if (array_key_exists($method_name, $this->methods)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'a method already exists on this PhPClass with name "%s"',
                    $method_name
                )
            );
        }

        $this->methods[$method_name] = $method;

        return $method_name;
    }

    public function addImplement($new_implement)
    {
        $found = false;
        foreach ($this->implements as $impl) {
            if ($impl === $new_implement) {
                $found = true;
            }
        }

        if (!$found) {
            $this->implements[] = $new_implement;
        }
    }

    public function getImplements()
    {
        return $this->implements;
    }

    /**
     * @return mixed
     */
    public function getExtends()
    {
        return $this->extends;
    }

    /**
     * @param mixed $extends
     */
    public function setExtends($extends)
    {
        $this->extends = $extends;
    }

    public function getProperty($property_name)
    {
        if (array_key_exists($property_name, $this->properties)) {
            return $this->properties[$property_name];
        }

        return;
    }

    public function getMethod($method_name)
    {
        foreach ($this->methods as $method) {
            if ($method_name == $method->getName()) {
                return $method;
            }
        }

        return;
    }

    /**
     * Just a shortcut to help make the constructor.
     *
     * It add a property on the class, generates a constructor if none
     * exists, and adds the argument + sets the proeprty on construction.
     *
     * @param $property
     * @param $type
     */
    public function addDependencyInjection($property, $type)
    {
        $this->addProperty($property, 'protected');

        // if this class has no constructor, we need to make one
        if (!$constructor = $this->getMethod('__construct')) {
            $constructor = new PhpMethod();
            $constructor->setName('__construct');
            $this->addMethod($constructor);
        }

        $constructor->addArgument($property, $type);

        $code = $constructor->getCode();
        $code .= '$this->'.$property.' = $'.$property.';';
        $constructor->setCode($code);
    }
}
