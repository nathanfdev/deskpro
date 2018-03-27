<?php

namespace DeskPRO\Bundle\SendmailBundle\Twig\PreProcessor;

class EmailPreProcessor extends AbstractPreProcessor
{
    public function process($source, $name = null)
    {
        $source = preg_replace(
            '/<spacer\s*([^>]*)\/>/',
            '{% spacer $1 %}{% endspacer %}',
            $source
        );
        $source = preg_replace(
            '/<(wrapper|container|row|columns|spacer|callout)\s*([^>]*)>/',
            '{% $1 $2 %}',
            $source
        );
        $source = preg_replace(
            '/<\/(wrapper|container|row|columns|spacer|callout)\s*>/',
            '{% end$1 %}',
            $source
        );

        return $source;
    }
}
