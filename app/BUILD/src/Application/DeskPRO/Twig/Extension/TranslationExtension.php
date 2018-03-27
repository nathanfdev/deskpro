<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Twig\Extension;

use Symfony\Bridge\Twig\Extension\TranslationExtension as BaseTranslationExtension;

/**
 * We do not use Twigs default translate, but its always on. This just nulls the effects.
 */
class TranslationExtension extends BaseTranslationExtension
{
    public function trans($message, array $arguments = [], $domain = 'messages', $locale = null)
    {
        return $message;
    }

    public function transchoice($message, $count, array $arguments = [], $domain = 'messages', $locale = null)
    {
        return $message;
    }
}
