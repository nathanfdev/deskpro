<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\View\Pagerfanta\Template;

use Pagerfanta\View\Template\Template;

class DeskproTemplate extends Template
{
    protected static $defaultOptions = [
        'prev_message'        => '&larr; Previous',
        'next_message'        => 'Next &rarr;',
        'dots_message'        => '&hellip;',
        'active_suffix'       => '',
        'css_container_class' => 'pagination',
        'css_prev_class'      => 'prev',
        'css_next_class'      => 'next',
        'css_disabled_class'  => 'disabled',
        'css_dots_class'      => 'disabled',
        'css_active_class'    => 'active-page',
    ];

    public function container()
    {
        return sprintf('<ul class="pagination">%%pages%%</ul>', $this->option('css_container_class'));
    }

    public function page($page)
    {
        $text = $page;

        return $this->pageWithText($page, $text);
    }

    public function pageWithText($page, $text)
    {
        $class = null;

        return $this->pageWithTextAndClass($page, $text, $class);
    }

    private function pageWithTextAndClass($page, $text, $class)
    {
        $href = $this->generateRoute($page);

        return $this->linkLi($class, $href, $text);
    }

    public function previousDisabled()
    {
        $class = $this->previousDisabledClass();
        $text  = $this->option('prev_message');

        return $this->spanLi($class, $text);
    }

    private function previousDisabledClass()
    {
        return $this->option('css_prev_class').' '.$this->option('css_disabled_class');
    }

    public function previousEnabled($page)
    {
        $text  = $this->option('prev_message');
        $class = $this->option('css_prev_class');

        return $this->pageWithTextAndClass($page, $text, $class);
    }

    public function nextDisabled()
    {
        $class = $this->nextDisabledClass();
        $text  = $this->option('next_message');

        return $this->spanLi($class, $text);
    }

    private function nextDisabledClass()
    {
        return $this->option('css_next_class').' '.$this->option('css_disabled_class');
    }

    public function nextEnabled($page)
    {
        $text  = $this->option('next_message');
        $class = $this->option('css_next_class');

        return $this->pageWithTextAndClass($page, $text, $class);
    }

    public function first()
    {
        return $this->page(1);
    }

    public function last($page)
    {
        return $this->page($page);
    }

    public function current($page)
    {
        $text  = trim($page.' '.$this->option('active_suffix'));
        $class = $this->option('css_active_class');

        return $this->spanLi($class, $text);
    }

    public function separator()
    {
        $class = $this->option('css_dots_class');
        $text  = $this->option('dots_message');

        return $this->spanLi($class, $text);
    }

    private function linkLi($class, $href, $text)
    {
        $liClass = $class ? sprintf(' class="%s"', $class) : '';

        $href = $this->removeQueryParams($href, ['lang_url_code', 'brand_id', 'theme_set_id']);

        return sprintf('<li%s><a href="%s">%s</a></li>', $liClass, $href, $text);
    }

    private function spanLi($class, $text)
    {
        $liClass = $class ? sprintf(' class="%s"', $class) : '';

        return sprintf('<li%s><span>%s</span></li>', $liClass, $text);
    }

    private function unparse_url($parsed_url)
    {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':'.$parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':'.$parsed_url['pass'] : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?'.$parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#'.$parsed_url['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    /**
     * Remove params (if they exist) from a URL string's query.
     *
     * @param string $url              source url
     * @param array  $params_to_remove
     *
     * @return string result url
     */
    private function removeQueryParams($url, array $params_to_remove)
    {
        $parsed = parse_url($url);
        if ($parsed && isset($parsed['query'])) {
            $parsed['query'] = implode('&', array_filter(explode('&', $parsed['query']), function ($param) use ($params_to_remove) {
                $param_name = explode('=', $param)[0];
                if ($param_name === 'page' && explode('=', $param)[1] == 1) {
                    return false;
                }

                return !in_array($param_name, $params_to_remove);
            }));
            if ($parsed['query'] === '') {
                unset($parsed['query']);
            }

            return $this->unparse_url($parsed);
        } else {
            return $url;
        }
    }
}
