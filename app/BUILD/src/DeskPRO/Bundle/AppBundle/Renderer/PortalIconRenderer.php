<?php

namespace DeskPRO\Bundle\AppBundle\Renderer;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\CategoryAbstract;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\AppBundle\Entity\HasIconProperty;
use DeskPRO\Bundle\AppBundle\Entity\IconProperty;

class PortalIconRenderer
{
    public function getIconHtml(IconProperty $icon, $options)
    {
        $class = '';
        if (isset($options['class'])) {
            $class .= ' '.$options['class'];
        }
        if ($icon->getUrnNs() === 'urn:deskpro:local:blobs') {
            if ($icon->getBlob()) {
                return '<img src="'.$icon->getBlob()->getFileUrl().'" alt="icon" />';
            }
        }
        if ($icon->getUrnNs() === 'urn:deskpro:product:icons:fontawesome') {
            $iconOptions = $icon->getOptions();
            $iconStyle   = 'fas';
            if (isset($iconOptions['style'])) {
                $iconStyle = $iconOptions['style'];
            }

            return '<i class="'.$class.' '.$iconStyle.' '.$icon->getUrnPath().'"'.$this->compileStyle($options).'></i>';
        }

        return '';
    }

    public function getIconHtmlFrom(HasIconProperty $object, $options = [])
    {
        if ($object->getIcon()) {
            $icon = $this->getIconHtml($object->getIcon(), $options);
            if ($icon) {
                return $icon;
            }
        }
        $class = 'far file';
        if ($object instanceof CategoryAbstract) {
            $class = 'fas fa-folder';
        }
        if ($object instanceof Download) {
            $class = 'fas fa-download';
        }
        if ($object instanceof News) {
            $class = 'far fa-newspaper';
        }
        if ($object instanceof Article) {
            $class = 'far fa-file';
        }
        if ($object instanceof Topic) {
            $class = 'fas fa-book';
        }
        if (isset($options['class'])) {
            $class .= ' '.$options['class'];
        }

        return '<i class="'.$class.'"'.$this->compileStyle($options).'></i>';
    }

    private function compileStyle($options)
    {
        if (!isset($options['style'])) {
            return '';
        }
        $styles = [];
        foreach ($options['style'] as $style => $value) {
            $styles[] = "$style:$value";
        }

        return ' style='.implode('; ', $styles);
    }
}
