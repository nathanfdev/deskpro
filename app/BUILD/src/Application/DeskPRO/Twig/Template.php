<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Twig;

abstract class Template extends \Twig_Template
{
    public function display(array $context, array $blocks = [])
    {
        if (!$this->env->isCustomTemplate($this->getTemplateName())) {
            parent::display($context, $blocks);
        } else {
            try {
                parent::display($context, $blocks);
            } catch (\Exception $e) {
                if (preg_match('#\.html\.twig$#', $this->getTemplateName())) {
                    echo "<div style='background-color: #ccc; border: 3px solid red; color: #000; padding: 10px; border-radius: 3px; margin: 10px;'>";
                    echo 'There was an error rendering your custom template: <strong>'.$this->getTemplateName().'</strong><br />';
                    echo '<hr/>';
                    echo $e->getMessage();
                    echo '</div>';
                } else {
                    echo "\n\n!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n\n";
                    echo 'There was an error rendering your customised template: '.$this->getTemplateName();
                    echo "\nError: ".$e->getMessage();
                    echo "\n\n!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n\n";
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($object, $item, array $arguments = [], $type = self::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
    {
        return parent::getAttribute($object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
    }
}
