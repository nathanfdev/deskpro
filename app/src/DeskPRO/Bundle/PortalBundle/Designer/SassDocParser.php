<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
     * @var string DP_ROOT relative or absolute path to sassdoc json file
     */
    private $variables_json_file_path;

    /**
     * @param string $variables_json_file_path
     */
    public function __construct($variables_json_file_path)
    {
        $this->variables_json_file_path = $variables_json_file_path;
    }

    /**
     * @throws \Exception
     *
     * @return array Variables specs
     */
    public function getVariableGroups()
    {
        return json_decode(file_get_contents($this->resolveFilePath()), true);
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
     * @return string
     *
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
     * @return array
     *
     */
    private function parseSizeVariable($value)
    {
        preg_match('/(.+)(px|em|pt|\%)/', $value, $matches);
        if (count($matches) < 3) {
            throw new \Exception("Can't parse variable of type size: $value");
        }

        return ['value' => floatval(trim($matches[1])), 'unit' => $matches[2]];
    }

    /**
     * @throws \Exception
     * @return string
     *
     */
    private function resolveFilePath()
    {
        $path = DP_ROOT.'/'.rtrim($this->variables_json_file_path, '/');

        if (file_exists($path)) {
            return $path;
        } elseif (file_exists($this->variables_json_file_path)) {
            return $this->variables_json_file_path;
        }

        throw new \Exception("Unable to resolve sass doc file {$this->variables_json_file_path}");
    }
}
