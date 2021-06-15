<?php

namespace Application\DeskPRO\Templating;

class TemplateUtils
{
    /**
     * Check if a template file path is allowed, if the template file is referencing
     * an actual path name
     *
     * @return bool Returns TRUE if allowed
     */
    public static function isAllowedTemplateFilepath($name)
    {
        global $DP_ENV;

        $templateName = (string) $name;

        if (file_exists($templateName)) {
            $isInBuildPath = strpos($templateName, $DP_ENV->getAppDir()) === 0;
            $isInVendorPath = strpos($templateName, $DP_ENV->getAppDir().'/vendor') === 0;
            $hasTwigFileExt = pathinfo($templateName, PATHINFO_EXTENSION) === 'twig';

            if ($isInBuildPath && $hasTwigFileExt) {
                return true;
            }

            if ($isInVendorPath) {
                return true;
            }

            return false;
        }

        // If the template file is not found on disk, let the proceeding logic run
        return true;
    }
}
