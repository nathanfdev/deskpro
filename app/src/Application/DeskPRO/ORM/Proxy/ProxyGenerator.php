<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\ORM\Proxy;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\Common\Proxy\Exception\UnexpectedValueException;
use Doctrine\Common\Util\ClassUtils;

class ProxyGenerator extends \Doctrine\Common\Proxy\ProxyGenerator
{
    /**
     * Used to match very simple id methods that don't need
     * to be decorated since the identifier is known.
     */
    const PATTERN_MATCH_ID_METHOD = '((public\s)?(function\s{1,}%s\s?\(\)\s{1,})\s{0,}{\s{0,}return\s{0,}\$this->%s;\s{0,}})i';

    /**
     * The namespace that contains all proxy classes.
     *
     * @var string
     */
    private $proxyNamespace;

    /**
     * The directory that contains all proxy classes.
     *
     * @var string
     */
    private $proxyDirectory;

    /**
     * Map of callables used to fill in placeholders set in the template.
     *
     * @var string[]|callable[]
     */
    protected $placeholders = array(
        'baseProxyInterface'   => 'Doctrine\Common\Proxy\Proxy',
        'additionalProperties' => '',
    );

    /**
     * Template used as a blueprint to generate proxies.
     *
     * @var string
     */
    protected $proxyClassTemplate = '<?php

namespace <namespace>;

class <proxyShortClassName> extends \<className> implements \<baseProxyInterface>
{
    public $__initializer__;
    public $__cloner__;
    public $__isInitialized__ = false;
    public $_dp_object_translatable;
    public static $lazyPropertiesDefaults = array(<lazyPropertiesDefaults>);

<additionalProperties>

<constructorImpl>

<magicGet>

<magicSet>

<magicIsset>

<sleepImpl>

<wakeupImpl>

<cloneImpl>

    public function __load() { $this->__initializer__ && $this->__initializer__->__invoke($this, \'__load\', array()); }
	public function __isInitialized() { return $this->__isInitialized__; }
	public function __setInitialized($initialized) { $this->__isInitialized__ = $initialized; }
    public function __setInitializer(\Closure $initializer = null) { $this->__initializer__ = $initializer; }
    public function __getInitializer() { return $this->__initializer__; }
	public function __setCloner(\Closure $cloner = null) { $this->__cloner__ = $cloner; }
	public function __getCloner() { return $this->__cloner__; }
    public function __getLazyProperties() { return self::$lazyPropertiesDefaults; }

    <methods>

    public function __getPropValue__($k) { return $this->$k; }
	public function __setPropValue__($k, $v) { $this->$k = $v; }
	public function __hasRunLoad__() { if (isset($this->__isInitialized__) && $this->__isInitialized__) return true; return false; }
}
';

    /**
     * Initializes a new instance of the <tt>ProxyFactory</tt> class that is
     * connected to the given <tt>EntityManager</tt>.
     *
     * @param string $proxyDirectory The directory to use for the proxy classes. It must exist.
     * @param string $proxyNamespace The namespace to use for the proxy classes.
     *
     * @throws InvalidArgumentException
     */
    public function __construct($proxyDirectory, $proxyNamespace)
    {
        if ( ! $proxyDirectory) {
            throw InvalidArgumentException::proxyDirectoryRequired();
        }

        if ( ! $proxyNamespace) {
            throw InvalidArgumentException::proxyNamespaceRequired();
        }

        $this->proxyDirectory        = $proxyDirectory;
        $this->proxyNamespace        = $proxyNamespace;
    }

    /**
     * Sets a placeholder to be replaced in the template.
     *
     * @param string          $name
     * @param string|callable $placeholder
     *
     * @throws InvalidArgumentException
     */
    public function setPlaceholder($name, $placeholder)
    {
        if ( ! is_string($placeholder) && ! is_callable($placeholder)) {
            throw InvalidArgumentException::invalidPlaceholder($name);
        }

        $this->placeholders[$name] = $placeholder;
    }

    /**
     * Sets the base template used to create proxy classes.
     *
     * @param string $proxyClassTemplate
     */
    public function setProxyClassTemplate($proxyClassTemplate)
    {
        $this->proxyClassTemplate = (string) $proxyClassTemplate;
    }

    /**
     * Generates a proxy class file.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class    Metadata for the original class.
     * @param string|bool                                        $fileName Filename (full path) for the generated class. If none is given, eval() is used.
     *
     * @throws UnexpectedValueException
     */
    public function generateProxyClass(ClassMetadata $class, $fileName = false)
    {
        preg_match_all('(<([a-zA-Z]+)>)', $this->proxyClassTemplate, $placeholderMatches);

        $placeholderMatches = array_combine($placeholderMatches[0], $placeholderMatches[1]);
        $placeholders       = array();

        foreach ($placeholderMatches as $placeholder => $name) {
            $placeholders[$placeholder] = isset($this->placeholders[$name])
                ? $this->placeholders[$name]
                : array($this, 'generate' . $name);
        }

        foreach ($placeholders as & $placeholder) {
            if (is_callable($placeholder)) {
                $placeholder = call_user_func($placeholder, $class);
            }
        }

        $proxyCode = strtr($this->proxyClassTemplate, $placeholders);

        if ( ! $fileName) {
            $proxyClassName = $this->generateNamespace($class) . '\\' . $this->generateProxyShortClassName($class);

            if ( ! class_exists($proxyClassName)) {
                eval(substr($proxyCode, 5));
            }

            return;
        }

        $parentDirectory = dirname($fileName);

        if ( ! is_dir($parentDirectory) && (false === @mkdir($parentDirectory, 0775, true))) {
            throw UnexpectedValueException::proxyDirectoryNotWritable();
        }

        if ( ! is_writable($parentDirectory)) {
            throw UnexpectedValueException::proxyDirectoryNotWritable();
        }

        $tmpFileName = $fileName . '.' . uniqid('', true);

        file_put_contents($tmpFileName, $proxyCode);
        rename($tmpFileName, $fileName);
    }

    /**
     * Generates the proxy short class name to be used in the template.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return string
     */
    private function generateProxyShortClassName(ClassMetadata $class)
    {
        $proxyClassName = ClassUtils::generateProxyClassName($class->getName(), $this->proxyNamespace);
        $parts          = explode('\\', strrev($proxyClassName), 2);

        return strrev($parts[0]);
    }

    /**
     * Generates the proxy namespace.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return string
     */
    private function generateNamespace(ClassMetadata $class)
    {
        $proxyClassName = ClassUtils::generateProxyClassName($class->getName(), $this->proxyNamespace);
        $parts = explode('\\', strrev($proxyClassName), 2);

        return strrev($parts[1]);
    }

    /**
     * Generates the original class name.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return string
     */
    private function generateClassName(ClassMetadata $class)
    {
        return ltrim($class->getName(), '\\');
    }

    /**
     * Generates the array representation of lazy loaded public properties and their default values.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return string
     */
    private function generateLazyPropertiesDefaults(ClassMetadata $class)
    {
        $lazyPublicProperties = $this->getLazyLoadedPublicProperties($class);
        $values               = array();

        foreach ($lazyPublicProperties as $key => $value) {
            $values[] = var_export($key, true) . ' => ' . var_export($value, true);
        }

        return implode(', ', $values);
    }

    /**
     * Generates the constructor code (un-setting public lazy loaded properties, setting identifier field values).
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return string
     */
    private function generateConstructorImpl(ClassMetadata $class)
    {
        $constructorImpl = <<<'EOT'
    public function __construct($initializer = null, $cloner = null)
    {

EOT;
        $toUnset = array();

        foreach ($this->getLazyLoadedPublicProperties($class) as $lazyPublicProperty => $unused) {
            $toUnset[] = '$this->' . $lazyPublicProperty;
        }

        $constructorImpl .= (empty($toUnset) ? '' : '        unset(' . implode(', ', $toUnset) . ");\n")
            . <<<'EOT'

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }
EOT;

        return $constructorImpl;
    }

    /**
     * Generates the magic getter invoked when lazy loaded public properties are requested.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return string
     */
    private function generateMagicGet(ClassMetadata $class)
    {
        $lazyPublicProperties = array_keys($this->getLazyLoadedPublicProperties($class));
        $reflectionClass      = $class->getReflectionClass();
        $hasParentGet         = false;
        $returnReference      = '';

        if ($reflectionClass->hasMethod('__get')) {
            $hasParentGet = true;

            if ($reflectionClass->getMethod('__get')->returnsReference()) {
                $returnReference = '& ';
            }
        }

        if (empty($lazyPublicProperties) && ! $hasParentGet) {
            return '';
        }

        $magicGet = <<<EOT
    public function {$returnReference}__get(\$name)
    {

EOT;

        if ( ! empty($lazyPublicProperties)) {
            $magicGet .= <<<'EOT'
        if (array_key_exists($name, $this->__getLazyProperties())) {
            $this->__initializer__ && $this->__initializer__->__invoke($this, '__get', array($name));

            return $this->$name;
        }


EOT;
        }

        if ($hasParentGet) {
            $magicGet .= <<<'EOT'
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__get', array($name));

        return parent::__get($name);

EOT;
        } else {
            $magicGet .= <<<'EOT'
        trigger_error(sprintf('Undefined property: %s::$%s', __CLASS__, $name), E_USER_NOTICE);

EOT;
        }

        $magicGet .= "    }";

        return $magicGet;
    }

    /**
     * Generates the magic setter (currently unused).
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return string
     */
    private function generateMagicSet(ClassMetadata $class)
    {
        $lazyPublicProperties = $this->getLazyLoadedPublicProperties($class);
        $hasParentSet         = $class->getReflectionClass()->hasMethod('__set');

        if (empty($lazyPublicProperties) && ! $hasParentSet) {
            return '';
        }

        $magicSet   = <<<EOT
    public function __set(\$name, \$value)
    {

EOT;

        if ( ! empty($lazyPublicProperties)) {
            $magicSet .= <<<'EOT'
        if (array_key_exists($name, $this->__getLazyProperties())) {
            $this->__initializer__ && $this->__initializer__->__invoke($this, '__set', array($name, $value));

            $this->$name = $value;

            return;
        }


EOT;
        }

        if ($hasParentSet) {
            $magicSet .= <<<'EOT'
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__set', array($name, $value));

        return parent::__set($name, $value);
EOT;
        } else {
            $magicSet .= "        \$this->\$name = \$value;";
        }

        $magicSet .= "\n    }";

        return $magicSet;
    }

    /**
     * Generates the magic issetter invoked when lazy loaded public properties are checked against isset().
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return string
     */
    private function generateMagicIsset(ClassMetadata $class)
    {
        $lazyPublicProperties = array_keys($this->getLazyLoadedPublicProperties($class));
        $hasParentIsset       = $class->getReflectionClass()->hasMethod('__isset');

        if (empty($lazyPublicProperties) && ! $hasParentIsset) {
            return '';
        }

        $magicIsset = <<<EOT
    public function __isset(\$name)
    {

EOT;

        if ( ! empty($lazyPublicProperties)) {
            $magicIsset .= <<<'EOT'
        if (array_key_exists($name, $this->__getLazyProperties())) {
            $this->__initializer__ && $this->__initializer__->__invoke($this, '__isset', array($name));

            return isset($this->$name);
        }


EOT;
        }

        if ($hasParentIsset) {
            $magicIsset .= <<<'EOT'
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__isset', array($name));

        return parent::__isset($name);

EOT;
        } else {
            $magicIsset .= "        return false;";
        }

        return $magicIsset . "\n    }";
    }

    /**
     * Generates implementation for the `__sleep` method of proxies.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return string
     */
    private function generateSleepImpl(ClassMetadata $class)
    {
        $hasParentSleep = $class->getReflectionClass()->hasMethod('__sleep');
        $inheritDoc     = $hasParentSleep ? '{@inheritDoc}' : '';
        $sleepImpl      = <<<EOT
    public function __sleep()
    {

EOT;

        if ($hasParentSleep) {
            return $sleepImpl . <<<'EOT'
        $properties = array_merge(array('__isInitialized__'), parent::__sleep());

        if ($this->__isInitialized__) {
            $properties = array_diff($properties, array_keys($this->__getLazyProperties()));
        }

        return $properties;
    }
EOT;
        }

        $allProperties = array('__isInitialized__');

        /* @var $prop \ReflectionProperty */
        foreach ($class->getReflectionClass()->getProperties() as $prop) {
            $allProperties[] = $prop->getName();
        }

        $lazyPublicProperties = array_keys($this->getLazyLoadedPublicProperties($class));
        $protectedProperties  = array_diff($allProperties, $lazyPublicProperties);

        foreach ($allProperties as &$property) {
            $property = var_export($property, true);
        }

        foreach ($protectedProperties as &$property) {
            $property = var_export($property, true);
        }

        $allProperties       = implode(', ', $allProperties);
        $protectedProperties = implode(', ', $protectedProperties);

        return $sleepImpl . <<<EOT
        if (\$this->__isInitialized__) {
            return array($allProperties);
        }

        return array($protectedProperties);
    }
EOT;
    }

    /**
     * Generates implementation for the `__wakeup` method of proxies.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return string
     */
    private function generateWakeupImpl(ClassMetadata $class)
    {
        $unsetPublicProperties = array();
        $hasWakeup             = $class->getReflectionClass()->hasMethod('__wakeup');

        foreach (array_keys($this->getLazyLoadedPublicProperties($class)) as $lazyPublicProperty) {
            $unsetPublicProperties[] = '$this->' . $lazyPublicProperty;
        }

        $shortName  = $this->generateProxyShortClassName($class);
        $inheritDoc = $hasWakeup ? '{@inheritDoc}' : '';
        $wakeupImpl = <<<EOT
    public function __wakeup()
    {
        if ( ! \$this->__isInitialized__) {
            \$this->__initializer__ = function ($shortName \$proxy) {
                \$proxy->__setInitializer(null);
                \$proxy->__setCloner(null);

                \$existingProperties = get_object_vars(\$proxy);

                foreach (\$proxy->__getLazyProperties() as \$property => \$defaultValue) {
                    if ( ! array_key_exists(\$property, \$existingProperties)) {
                        \$proxy->\$property = \$defaultValue;
                    }
                }
            };

EOT;

        if ( ! empty($unsetPublicProperties)) {
            $wakeupImpl .= "\n            unset(" . implode(', ', $unsetPublicProperties) . ");";
        }

        $wakeupImpl .= "\n        }";

        if ($hasWakeup) {
            $wakeupImpl .= "\n        parent::__wakeup();";
        }

        $wakeupImpl .= "\n    }";

        return $wakeupImpl;
    }

    /**
     * Generates implementation for the `__clone` method of proxies.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return string
     */
    private function generateCloneImpl(ClassMetadata $class)
    {
        $hasParentClone  = $class->getReflectionClass()->hasMethod('__clone');
        $inheritDoc      = $hasParentClone ? '{@inheritDoc}' : '';
        $callParentClone = $hasParentClone ? "\n        parent::__clone();\n" : '';

        return <<<EOT
    public function __clone()
    {
        \$this->__cloner__ && \$this->__cloner__->__invoke(\$this, '__clone', array());
$callParentClone    }
EOT;
    }

    /**
     * Generates decorated methods by picking those available in the parent class.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return string
     */
    private function generateMethods(ClassMetadata $class)
    {
        $methods           = '';
        $methodNames       = array();
        $reflectionMethods = $class->getReflectionClass()->getMethods(\ReflectionMethod::IS_PUBLIC);
        $skippedMethods    = array(
            '__sleep'   => true,
            '__clone'   => true,
            '__wakeup'  => true,
            '__get'     => true,
            '__set'     => true,
            '__isset'   => true,

			'__getpropvalue__' => true,
			'__setpropvalue__' => true,
			'__hasrunload__' => true,
			'addcustomcallable' => true,
			'getobjecttranslatable' => true,
			'ensuredefaultpropertychangedlistener' => true,
			'addpropertychangedlistener' => true,
			'removepropertychangedlistener' => true,
        );

        foreach ($reflectionMethods as $method) {
            $name = $method->getName();

            if (
                $method->isConstructor() ||
                isset($skippedMethods[strtolower($name)]) ||
                isset($methodNames[$name]) ||
                $method->isFinal() ||
                $method->isStatic() ||
                ( ! $method->isPublic())
            ) {
                continue;
            }

            $methodNames[$name] = true;
            $methods .= ""
                . '    public function ';

            if ($method->returnsReference()) {
                $methods .= '&';
            }

            $methods .= $name . '(';

            $firstParam      = true;
            $parameterString = '';
            $argumentString  = '';
            $parameters      = array();

            foreach ($method->getParameters() as $param) {
                if ($firstParam) {
                    $firstParam = false;
                } else {
                    $parameterString .= ', ';
                    $argumentString  .= ', ';
                }

                try {
                    $paramClass = $param->getClass();
                } catch (\ReflectionException $previous) {
                    throw UnexpectedValueException::invalidParameterTypeHint(
                        $class->getName(),
                        $method->getName(),
                        $param->getName(),
                        $previous
                    );
                }

                // We need to pick the type hint class too
                if (null !== $paramClass) {
                    $parameterString .= '\\' . $paramClass->getName() . ' ';
                } elseif ($param->isArray()) {
                    $parameterString .= 'array ';
                } elseif (method_exists($param, 'isCallable') && $param->isCallable()) {
                    $parameterString .= 'callable ';
                }

                if ($param->isPassedByReference()) {
                    $parameterString .= '&';
                }

                $parameters[] = '$' . $param->getName();
                $parameterString .= '$' . $param->getName();
                $argumentString  .= '$' . $param->getName();

                if ($param->isDefaultValueAvailable()) {
                    $parameterString .= ' = ' . var_export($param->getDefaultValue(), true);
                }
            }

            $methods .= $parameterString . ')';
            $methods .= "\n" . '    {' . "\n";

            if ($this->isShortIdentifierGetter($method, $class)) {
                $identifier = lcfirst(substr($name, 3));
                $fieldType  = $class->getTypeOfField($identifier);
                $cast       = in_array($fieldType, array('integer', 'smallint')) ? '(int) ' : '';

                $methods .= '        if ($this->__isInitialized__ === false) {' . "\n";
                $methods .= '            return ' . $cast . ' parent::' . $method->getName() . "();\n";
                $methods .= '        }' . "\n\n";
            }

            $methods .= "\n        \$this->__initializer__ "
                . "&& \$this->__initializer__->__invoke(\$this, " . var_export($name, true)
                . ", array(" . implode(', ', $parameters) . "));"
                . "\n\n        return parent::" . $name . '(' . $argumentString . ');'
                . "\n" . '    }' . "\n";
        }

        return $methods;
    }

    /**
     * Generates the Proxy file name.
     *
     * @param string $className
     * @param string $baseDirectory Optional base directory for proxy file name generation.
     *                              If not specified, the directory configured on the Configuration of the
     *                              EntityManager will be used by this factory.
     *
     * @return string
     */
    public function getProxyFileName($className, $baseDirectory = null)
    {
        $baseDirectory = $baseDirectory ?: $this->proxyDirectory;

        return rtrim($baseDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . \Doctrine\Common\Proxy\Proxy::MARKER
            . str_replace('\\', '', $className) . '.php';
    }

    /**
     * Checks if the method is a short identifier getter.
     *
     * What does this mean? For proxy objects the identifier is already known,
     * however accessing the getter for this identifier usually triggers the
     * lazy loading, leading to a query that may not be necessary if only the
     * ID is interesting for the userland code (for example in views that
     * generate links to the entity, but do not display anything else).
     *
     * @param \ReflectionMethod                                  $method
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return boolean
     */
    private function isShortIdentifierGetter($method, ClassMetadata $class)
    {
        $identifier = lcfirst(substr($method->getName(), 3));
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        $cheapCheck = (
            $method->getNumberOfParameters() == 0
            && substr($method->getName(), 0, 3) == 'get'
            && in_array($identifier, $class->getIdentifier(), true)
            && $class->hasField($identifier)
            && (($endLine - $startLine) <= 4)
        );

        if ($cheapCheck) {
            $code = file($method->getDeclaringClass()->getFileName());
            $code = trim(implode(' ', array_slice($code, $startLine - 1, $endLine - $startLine + 1)));

            $pattern = sprintf(self::PATTERN_MATCH_ID_METHOD, $method->getName(), $identifier);

            if (preg_match($pattern, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates the list of public properties to be lazy loaded, with their default values.
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $class
     *
     * @return mixed[]
     */
    private function getLazyLoadedPublicProperties(ClassMetadata $class)
    {
        $defaultProperties = $class->getReflectionClass()->getDefaultProperties();
        $properties = array();

        foreach ($class->getReflectionClass()->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();

            if (($class->hasField($name) || $class->hasAssociation($name)) && ! $class->isIdentifier($name)) {
                $properties[$name] = $defaultProperties[$name];
            }
        }

        return $properties;
    }
}

