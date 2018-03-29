<?php

/**
 * Orb.
 *
 * @category Input
 */

namespace Orb\Input\Reader\Source;

use Orb\Util\OptionsArray;
use Orb\Util\Web;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * A reader source that fetches data from a superglobal array.
 */
class Superglobal implements SourceInterface, ResetSourceInterface
{
    /**
     * The superglobal name.
     *
     * @var string
     */
    protected $superglobal;

    /**
     * Array of data.
     *
     * @var array
     */
    protected $array = null;

    /**
     * @var \Orb\Util\OptionsArray
     */
    protected $options = [];

    /**
     * @var RequestStack|null
     */
    protected $request_stack;

    /**
     * Create the source.
     *
     * @param string       $sg_name       The name of the superglobal: _POST, _GET etc
     * @param array        $options
     * @param RequestStack $request_stack The symfony request stack for this request, if it exists
     */
    public function __construct($sg_name, array $options = null, RequestStack $request_stack = null)
    {
        $this->superglobal   = $sg_name;
        $this->options       = new OptionsArray($options ?: []);
        $this->request_stack = $request_stack;
    }

    public function resetSource()
    {
        $this->array = null;
    }

    /**
     * Get all data.
     *
     * @return array|null
     */
    public function getAll()
    {
        $this->_initArray();

        return $this->array;
    }

    /**
     * Get the value of some variable.
     *
     * @param string|array $name    The name of the variable
     * @param mixed        $options Any options there may be
     *
     * @return mixed
     */
    public function getValue($name, $options = null)
    {
        $this->_initArray();

        $parts = [];
        if (is_array($name)) {
            $parts = $name;
            $name  = array_shift($parts);
        }

        if (isset($this->array[$name])) {
            $value = $this->array[$name];
        } else {
            return;
        }

        if ($parts) {
            foreach ($parts as $part) {
                if (!is_array($value) or !isset($value[$part])) {
                    $value = null;
                    break;
                }

                $value = $value[$part];
            }
        }

        return $value;
    }

    protected function _initArray()
    {
        if ($this->array !== null) {
            return;
        } // already done

        // We'll enforce our own request array
        if ($this->superglobal == '_REQUEST') {
            $this->array = \array_merge($_GET, $this->_getPostArray());
        } else {
            if ($this->superglobal == '_POST') {
                $this->array = $this->_getPostArray();
            } else {
                $this->array = $GLOBALS[$this->superglobal];
            }
        }
        if (!$this->array) {
            $this->array = [];
        }
    }

    private function _getPostArray()
    {
        $post = $_POST;
        if ($this->options->get('accept_json_post') && in_array(Web::getRequestContentType(), ['application/json', 'text/x-json'])) {
            if (!$this->request_stack) {
                throw new \RuntimeException('the request_stack service should have been injected but was not, cannot read request data!');
            }
            $master_request = $this->request_stack->getMasterRequest();
            $json_post      = @json_decode($master_request->getContent(), true);
            if ($json_post && is_array($json_post)) {
                $post = array_merge($post, $json_post);
            }
        }

        return $post;
    }

    /**
     * Check if a value of some variable is set.
     *
     * @param string|array $name    The name of the variable
     * @param mixed        $options Any options there may be
     *
     * @return bool
     */
    public function checkIsset($name, $options = null)
    {
        return $this->getValue($name, $options) === null ? false : true;
    }

    /**
     * Get the superglobal name.
     *
     * @return string
     */
    public function getSuperglobalName()
    {
        return $this->superglobal;
    }
}
