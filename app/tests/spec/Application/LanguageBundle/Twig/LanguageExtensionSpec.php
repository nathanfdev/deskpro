<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace spec\Application\LanguageBundle\Twig;

use Application\DeskPRO\Translate\Translate;
use Application\LanguageBundle\Language\LanguageManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Application\LanguageBundle\Twig\LanguageExtension;

/**
 * @mixin \Application\LanguageBundle\Twig\LanguageExtension
 */
class LanguageExtensionSpec extends ObjectBehavior
{
    function let(LanguageManager $language_manager, Translate $translate)
    {
        $language_manager->getTranslator()->willReturn($translate);

        $this->beConstructedWith($language_manager);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldHaveType('\Twig_Extension');
        $this->getName()->shouldBe('phrase_extension');
    }

    function it_uses_the_translator_from_language_stack_to_resolve_phrases(
        \Twig_Environment $twig,
        Translate $translate
    )
    {
        $translate->phrase('portal.phrase.here', array('name' => 'Chris Tickner', '_context' => 'context'))
            ->willReturn('hi Chris Tickner');

        $this->getPhrase($twig, 'context', 'portal.phrase.here', array('name' => 'Chris Tickner'), true)
            ->shouldReturn('hi Chris Tickner')
        ;
    }
}
