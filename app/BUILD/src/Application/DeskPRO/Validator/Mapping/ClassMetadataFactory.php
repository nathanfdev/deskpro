<?php

/**
 * DeskPRO.
 *
 * @category Validator
 */

namespace Application\DeskPRO\Validator\Mapping;

use Orb\Util\Strings;
use Orb\Util\Util;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;

/**
 * This custom factory is the same as the default, except we intercept constraints and prefix the default
 * error messages with the classname. For example, "This value should not be blank." becomes "[NotBlank] This value should not be blank.".
 *
 * We do this so we can add code-names to validators. When we render validator errors (see ViolationApiRenderer), we then parse out the
 * code names.
 *
 * Templates/API will use code names. But the readable English error messages are good as well (for example, makes API more human-friendly).
 */
class ClassMetadataFactory extends LazyLoadingMetadataFactory
{
    /**
     * Array of classnames we've done.
     *
     * @var array
     */
    private $done_classes = [];

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($value)
    {
        if (!is_object($value) && !is_string($value)) {
            throw new NoSuchMetadataException(sprintf('Cannot create metadata for non-objects. Got: %s', gettype($value)));
        }

        $class    = ltrim(is_object($value) ? get_class($value) : $value, '\\');
        $metadata = parent::getMetadataFor($value);

        if (isset($this->done_classes[$class])) {
            return $metadata;
        }

        $this->done_classes[$class] = true;

        foreach ($metadata->constraints as $c) {
            $this->procConstraint($c);
        }
        foreach ($metadata->getters as $g) {
            foreach ($g->constraints as $c) {
                $this->procConstraint($c);
            }
        }
        foreach ($metadata->properties as $p) {
            foreach ($p->constraints as $c) {
                $this->procConstraint($c);
            }
        }
        foreach ($metadata->members as $m) {
            if ($m && !empty($m->constraints)) {
                foreach ($m->constraints as $c) {
                    $this->procConstraint($c);
                }
            }
        }

        return $metadata;
    }

    private function procConstraint($constraint)
    {
        foreach ($constraint as $prop => $val) {
            if (($prop == 'message' || preg_match('#Message$#', $prop)) && is_string($val) && !preg_match('#^\[[a-zA-Z0-9_\-\.]+\]#', $val)) {
                $name = Util::getBaseClassname($constraint);
                $name = preg_replace('#Constraint$#', '', $name); // some have a Constraint suffix which we dont want
                $name = Strings::camelCaseToUnderscore($name);

                // This is for case when we have pluralization cases for constraint message that are divided by '|'
                if (strpos($val, '|') !== false) {
                    $parts = explode('|', $val);

                    foreach ($parts as $key => $part) {
                        $parts[$key] = '['.$name.'] '.$part;
                    }

                    $val               = implode('|', $parts);
                    $constraint->$prop = $val;
                } else {
                    $constraint->$prop = '['.$name.'] '.$val;
                }
            }
        }

        // Some constraints are collections of other constraints (collection related validators)
        if (!empty($constraint->constraints)) {
            foreach ($constraint->constraints as $c) {
                $this->procConstraint($c);
            }
        }
    }
}
