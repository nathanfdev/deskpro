<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Twig\Extension;

use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\FormView;

class FormExtension extends \Symfony\Bridge\Twig\Extension\FormExtension
{
    public function __construct(array $resources = [])
    {
        $set_resources = [];
        foreach ($resources as $r) {
            if ($r != 'form_div_layout.html.twig') {
                $set_resources[] = $r;
            }
        }

        parent::__construct($set_resources);
    }

    protected function render(FormView $view, $section, array $variables = [])
    {
        $mainTemplate = in_array($section, ['widget', 'row']);

        if (null === $this->template) {
            $this->template = reset($this->resources);
            if (!$this->template instanceof \Twig_Template) {
                $this->template = $this->environment->loadTemplate($this->template);
            }
        }

        $custom    = '_'.$view->get('id');
        $rendering = $custom.$section;
        $blocks    = $this->getBlocks($view);

        if (isset($this->varStack[$rendering])) {
            $typeIndex                               = $this->varStack[$rendering]['typeIndex'] - 1;
            $types                                   = $this->varStack[$rendering]['types'];
            $this->varStack[$rendering]['variables'] = array_replace_recursive($this->varStack[$rendering]['variables'], $variables);
        } else {
            $types                      = $view->get('types');
            $types[]                    = $custom;
            $typeIndex                  = count($types) - 1;
            $this->varStack[$rendering] = [
                'variables' => array_replace_recursive($view->all(), $variables),
                'types'     => $types,
            ];
        }

        do {
            $types[$typeIndex] .= '_'.$section;

            if (isset($blocks[$types[$typeIndex]])) {
                $this->varStack[$rendering]['typeIndex'] = $typeIndex;

                // we do not call renderBlock here to avoid too many nested level calls (XDebug limits the level to 100 by default)
                ob_start();
                $this->template->displayBlock($types[$typeIndex], $this->varStack[$rendering]['variables'], $blocks);
                $html = ob_get_clean();

                if ($mainTemplate) {
                    $view->setRendered();
                }

                unset($this->varStack[$rendering]);

                return $html;
            }
        } while (--$typeIndex >= 0);

        throw new FormException(sprintf(
            'Unable to render the form as none of the following blocks exist: "%s".',
            implode('", "', array_reverse($types))
        ));
    }
}
