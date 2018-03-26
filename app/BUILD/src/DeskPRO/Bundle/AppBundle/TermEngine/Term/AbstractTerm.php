<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\ValidTermEngineTerm;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ValidTermEngineTerm()
 * @JMS\ExclusionPolicy("all")
 */
abstract class AbstractTerm implements TermInterface
{
    /**
     * @var TermOptionsResolver[]
     */
    private static $options_resolvers = [];

    /**
     * Term operation.
     *
     * @var string the op for this term
     *
     * The op MUST be a supported TermInterface::OP_* constant
     *
     * @Assert\NotNull
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $op;

    /**
     * This is where all of the data of a term is stored.
     *
     * @var array the defined settings
     *
     * Note that default options are not stored here. We only store options
     * that were explicitly added. The only access to options is via the
     * getOptions() method (which uses OptionsResolver to resolve the options)
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     */
    protected $options;

    /**
     * @param array  $options initial settings
     * @param string $op      initialize op
     */
    public function __construct(array $options = [], $op = null)
    {
        $this->options = $options;

        if (null !== $op) {
            $this->setOp($op);
        } else {
            $this->setOp($this->getDefaultOp());
        }
    }

    /**
     * Implements the TermInterface by providing a pseudo-abstract way of creating the options resolver by
     * offering concretes the convenience of only making a configureOptions() method to initially create their
     * options resolver.
     *
     * Only creates one instance per class, and hands off to configureOptions() to set it up.
     *
     * @return TermOptionsResolver
     */
    public static function getOptionsResolver()
    {
        $cname = get_called_class();
        if (!array_key_exists($cname, self::$options_resolvers)) {
            $resolver = new TermOptionsResolver();
            static::configureOptions($resolver);
            self::$options_resolvers[$cname] = $resolver;
        }

        return self::$options_resolvers[$cname];
    }

    /**
     * Configure your options here. You MUST override this method, even if you are not configuring any options.
     *
     * @param TermOptionsResolver $resolver
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        throw new \RuntimeException('Term\'s extending AbstractTerm must override the "configureOptions" method');
    }

    /**
     * {@inheritdoc}
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * {@inheritdoc}
     */
    public function setOp($op)
    {
        $this->op = (string) $op;
    }

    /**
     * Serialize the user-given data.
     *
     * Note that we don't serialize the resolved options.
     *
     * @return array
     */
    public function serialize()
    {
        return [
            'op'      => $this->op,
            'options' => $this->options,
        ];
    }

    /**
     * Unserialize the serialized data.
     *
     * @param $serialized
     */
    public function unserialize($serialized)
    {
        $this->op      = $serialized['op'];
        $this->options = $serialized['options'];
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return static::getOptionsResolver()->resolve($this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * @return array
     */
    public function getOptionConstraints()
    {
        return static::getOptionsResolver()->getConstraints();
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($option)
    {
        return array_key_exists($option, $this->getOptions());
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($option)
    {
        $options = $this->getOptions();

        return $options[$option];
    }

    /**
     * {@inheritdoc}
     */
    public function replaceOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        foreach ($options as $option => $value) {
            $this->setOption($option, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeOption($option)
    {
        if (array_key_exists($option, $this->options)) {
            unset($this->options[$option]);
        }
    }

    /**
     * AbstractTerm lets you get the raw options that were set. This is NOT recommended to be
     * used for getting options because these raw options are NOT resolved. This method is here only for meta-info about the user-defined term options. This is deviation from the TermInterface.
     *
     * See getOptions() and getOption() to get the real options
     *
     * @return array
     *
     * @deprecated use getOptions() instead
     */
    public function getRawOptions()
    {
        return $this->options;
    }
}
