<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Designer;

/**
 * Class SassDocParser.
 */
class SassDocParser
{
    /**
     * @var string DP_WEB_ROOT relative or absolute path to sassdoc json file
     */
    private $variables_json_file_path;

    /**
     * @param string $variables_json_file_path
     *
     * @throws \Exception
     */
    public function __construct($variables_json_file_path)
    {
        if (!$this->variables_json_file_path = realpath($variables_json_file_path)) {
            throw new \Exception("Unable to resolve sass doc file {$this->variables_json_file_path}");
        }
    }

    /**
     * @throws \Exception
     *
     * @return array Variables specs
     */
    public function getVariableGroups()
    {
        return json_decode(file_get_contents($this->variables_json_file_path), true);
    }

    /**
     * Get sass doc variable values in the atomic format (i.e. ['value' => 14, 'unit' => 'px']).
     *
     * @return array
     */
    public function getVariableValues()
    {
        $groups    = $this->getVariableGroups();
        $variables = call_user_func_array('array_merge', $groups);
        $values    = [];
        foreach ($variables as $variable) {
            $values[$variable['name']] = $this->cssTovalue($variable);
        }

        return $values;
    }

    /**
     * @param array $variable
     *
     * @throws \Exception
     *
     * @return string
     */
    private function cssToValue(array $variable)
    {
        switch ($variable['type']) {
            case 'color':
            case 'float':
            case 'font':
                return $variable['default_value'];
            case 'size':
                return $this->parseSizeVariable($variable['default_value']);
            default:
                throw new \Exception("Unknown variable type {$variable['type']}");
        }
    }

    /**
     * @param string $value
     *
     * @throws \Exception
     *
     * @return array
     */
    private function parseSizeVariable($value)
    {
        preg_match('/(.+)(px|em|pt|\%)/', $value, $matches);
        if (count($matches) < 3) {
            throw new \Exception("Can't parse variable of type size: $value");
        }

        return ['value' => floatval(trim($matches[1])), 'unit' => $matches[2]];
    }
}
