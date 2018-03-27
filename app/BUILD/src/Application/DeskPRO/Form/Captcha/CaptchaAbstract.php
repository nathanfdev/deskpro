<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Form\Captcha;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class CaptchaAbstract
{
    /**
     * The service container.
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * Array of options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param array                                                     $options
     */
    final public function __construct(ContainerInterface $container, array $options = [])
    {
        $this->container = $container;
        $this->options   = $options;

        $this->init();
    }

    protected function init()
    {
        // empty construct hook
    }

    /**
     * Get the captcha HTML to render into the form page.
     *
     * @return string
     */
    abstract public function getHtml();

    /**
     * Validate an incoming and make sure the captcha is correct.
     *
     * @return bool
     */
    abstract public function validate();

    /**
     * Get an option.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Set an options.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return string
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Set many options at once.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Check if an option is set.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    /**
     * @return \Symfony\Component\Templating\EngineInterface
     */
    protected function getTemplating()
    {
        return $this->container->get('templating');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getRequest()
    {
        return $this->container->get('request');
    }

    /**
     * @return \Application\DeskPRO\HttpFoundation\Session
     */
    protected function getSession()
    {
        return $this->container->get('session');
    }

    /**
     * @param string $name         The option to try and get first
     * @param string $setting_name If $name option doesnt exist, try to fetch it from settings
     *
     * @throws \RunTimeException
     *
     * @return mixed
     */
    protected function getOptionOrSetting($name, $setting_name)
    {
        if ($this->hasOption($name)) {
            return $this->getOption($name);
        } else {
            if (!$this->container->has('deskpro.core.settings')) {
                throw new \RunTimeException("No option `$name` provided, and there is no `deskpro.core.settings` to get setting `$setting_name`", 10);
            }

            $val = $this->container->get('deskpro.core.settings')->get($setting_name);
            if ($val === null) {
                throw new \RunTimeException("No option `$name` and `deskpro.core.settings` has no value for `$setting_name`", 20);
            }

            return $val;
        }
    }
}
