<?php

namespace DeskPRO\Bundle\AppBundle\Twig;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;

/**
 * Class LanguageExtension.
 */
class LanguageExtension extends \Twig_Extension
{
    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @param LanguageManager $languageManager
     */
    public function __construct(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'phrase',
                [$this, 'getPhrase'],
                [
                    'is_safe'           => ['all'],
                    'needs_context'     => true,
                    'needs_environment' => true,
                ]
            ),
            new \Twig_SimpleFunction(
                'object_phrase',
                [$this, 'getObjectPhrase'],
                [
                    'is_safe' => ['all'],
                ]
            ),
        ];
    }

    /**
     * @param \Twig_Environment $env
     * @param array             $context
     * @param string            $phrase_name
     * @param array             $vars
     * @param bool              $raw
     *
     * @throws \Twig_Error_Runtime
     *
     * @return mixed
     */
    public function getPhrase(\Twig_Environment $env, $context, $phrase_name, $vars = null, $raw = false)
    {
        if ($vars === null || !is_array($vars)) {
            $vars = [];
        }

        if (!$raw) {
            foreach ($vars as &$v) {
                $v = twig_escape_filter($env, $v, 'html');
            }
        }

        $vars['_context'] = $context;

        return $this->languageManager->phrase($phrase_name, $vars);
    }

    /**
     * @param object $object
     * @param bool   $property
     *
     * @return string
     */
    public function getObjectPhrase($object, $property = false)
    {
        return nl2br(htmlspecialchars($this->languageManager->objectPhrase($object, $property)));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'phrase_extension';
    }
}
