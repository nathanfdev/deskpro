<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
