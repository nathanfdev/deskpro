<?php

namespace DeskPRO\Bundle\AppBundle\Templating;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser as BaseTemplateNameParser;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Templating\TemplateReferenceInterface;

class TemplateNameParser extends BaseTemplateNameParser
{
    public function parse($name)
    {
        if ($name instanceof TemplateReferenceInterface) {
            return $name;
        } elseif (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        // normalize name
        $name = str_replace(':/', ':', preg_replace('#/{2,}#', '/', str_replace('\\', '/', $name)));

        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('Template name "%s" contains invalid characters.', $name));
        }

        if (!preg_match('/^([^:]*):([^:]*):(.+)\.([^\.]+)\.([^\.]+)$/', $name, $matches)) {
            return parent::parse($name);
        }

        // See also PortalLoader
        switch ($matches[1]) {
            case 'Theme':
            case 'ThemeParent':
            case 'ThemeTagTemplate':
                $template = new TemplateReference(null, $matches[2], $matches[3], $matches[4], $matches[5]);
                break;
            default:
                $template = new TemplateReference($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
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
