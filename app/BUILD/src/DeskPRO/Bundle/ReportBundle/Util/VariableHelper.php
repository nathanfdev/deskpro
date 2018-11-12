<?php

namespace DeskPRO\Bundle\ReportBundle\Util;

class VariableHelper
{
    /**
     * @param array $base
     * @param array $overrides
     */
    public static function mergeVariables(array $base, array $overrides)
    {
        $vars = [];
        foreach ($base as $v) {
            foreach ($overrides as $overrideVariable) {
                if ($overrideVariable['name'] === $v['name']) {
                    $v['value'] = $overrideVariable['value'];
                }
            }

            $vars[] = $v;
        }

        // add missing vars
        $names = array_map(function ($v) {
            return $v['name'];
        }, $vars);
        $names = array_combine($names, $names);
        foreach ($overrides as $v) {
            if (!isset($names[$v['name']])) {
                $vars[] = $v;
            }
        }

        return $vars;
    }
}
