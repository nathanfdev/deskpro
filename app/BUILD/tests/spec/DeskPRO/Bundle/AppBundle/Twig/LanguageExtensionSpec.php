<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\Twig;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Twig\LanguageExtension
 */
class LanguageExtensionSpec extends ObjectBehavior
{
    public function let(LanguageManager $language_manager)
    {
        $this->beConstructedWith($language_manager);
    }

    public function it_is_a_twig_extension()
    {
        $this->shouldHaveType('\Twig_Extension');
        $this->getName()->shouldBe('phrase_extension');
    }

    public function it_uses_the_translator_from_language_stack_to_resolve_phrases(
        \Twig_Environment $twig,
        LanguageManager $language_manager
    ) {
        $language_manager->phrase('portal.phrase.here', ['name' => 'Chris Tickner', '_context' => 'context'])
            ->willReturn('hi Chris Tickner');

        $this->getPhrase($twig, 'context', 'portal.phrase.here', ['name' => 'Chris Tickner'], true)
            ->shouldReturn('hi Chris Tickner')
        ;
    }
}
