<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SendmailBundle\Templating;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser as BaseTemplateNameParser;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference as BundleTemplateReference;
use Symfony\Component\Templating\TemplateReferenceInterface;

class TemplateNameParser extends BaseTemplateNameParser
{
    /**
     * {@inheritdoc}
     */
    public function parse($name)
    {
        if ($name instanceof TemplateReferenceInterface) {
            return $name;
        } elseif (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        // normalize name
        $name = str_replace(':/', ':', preg_replace('#/{2,}#', '/', strtr($name, '\\', '/')));

        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('Template name "%s" contains invalid characters.', $name));
        }

        if (!preg_match('/^([^:]*):([^:]*):(.+)\.([^\.]+)\.([^\.]+)$/', $name, $matches)) {
            throw new \InvalidArgumentException(sprintf('Template name "%s" is not valid (format is "bundle:section:template.format.engine").', $name));
        }

        if ($matches[1] == 'Apps') {
            $template = new BundleTemplateReference(null, $matches[2], $matches[3], $matches[4], $matches[5]);
        } else {
            $template = new BundleTemplateReference($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
        }

        if ($template->get('bundle')) {
            try {
                $this->kernel->getBundle($template->get('bundle'));
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(sprintf('Template name "%s" is not valid.', $name), 0, $e);
            }
        }

        return $this->cache[$name] = $template;
    }
}
