<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\View\PageTitle;

class PageTitleBuilder
{
    protected $default_sep;
    protected $section_sep;
    protected $section_parts;

    public function __construct(array $section_parts = [], $default_sep = ' - ', $section_sep = ' / ')
    {
        $this->section_parts = $section_parts;
        $this->default_sep   = $default_sep;
        $this->section_sep   = $section_sep;
    }

    public function __toString()
    {
        $sections = [];

        foreach ($this->section_parts as $section) {
            if (!is_array($section)) {
                continue;
            }
            if ($sec = implode($this->section_sep, $section)) {
                $sections[] = trim($sec);
            }
        }

        $title = trim(implode($this->default_sep, $sections));

        return $title;
    }

    public function appendSection($section)
    {
        $this->section_parts[] = $this->filterSection($section);
    }

    public function prependSection($section)
    {
        array_unshift($this->section_parts, $this->filterSection($section));
    }

    protected function filterSection($section)
    {
        if (!is_array($section)) {
            if (!empty($section)) {
                $section = [$section];
            }
        }

        return $section;
    }
}
