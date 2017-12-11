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

namespace spec\DeskPRO\Bundle\AppBundle\Language;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\EntityRepository\Language as LanguageRepo;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Language\LanguageStack;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Language\LanguageManager
 */
class LanguageManagerSpec extends ObjectBehavior
{
    public function let(
        Translate $translate,
        \DeskPRO\Bundle\AppBundle\Language\LanguageStack $language_stack,
        EntityManager $em
    ) {
        $this->beConstructedWith($translate, $language_stack, $em);
    }

    public function it_knows_if_is_multi_language_portal(
        EntityManager $em,
        LanguageRepo  $language_repo
    ) {
        $em->getRepository(Language::class)->willReturn($language_repo);
        $language_repo->countPortalLanguages()->willReturn(2);

        $this->isMultiLanguagePortal()->shouldBe(true);
    }

    public function it_knows_if_NOT_multi_language_portal(
        EntityManager $em,
        LanguageRepo  $language_repo
    ) {
        $em->getRepository(Language::class)->willReturn($language_repo);
        $language_repo->countPortalLanguages()->willReturn(1);

        $this->isMultiLanguagePortal()->shouldBe(false);
    }

    public function it_can_tell_you_if_a_lang_code_is_supported_by_this_portal(
        EntityManager $em,
        LanguageRepo $language_repo,
        Language $en,
        Language $fr
    ) {
        $em->getRepository(Language::class)->willReturn($language_repo);
        $language_repo->getForLangCode('en')->willReturn($en);
        $language_repo->getForLangCode('fr')->willReturn($fr);
        $language_repo->getForLangCode('it')->willReturn(null);

        $this->isLanguageSupported('en')->shouldReturn(true);
        $this->isLanguageSupported('fr')->shouldReturn(true);
        $this->isLanguageSupported('it')->shouldReturn(false);
    }

    public function it_can_give_you_the_language_stack_its_using(
        LanguageStack $language_stack
    ) {
        $this->getLanguageStack()->shouldReturn($language_stack);
    }

    public function it_will_give_you_an_array_of_all_enabled_languages(
        EntityManager $em,
        LanguageRepo $language_repo,
        Language $en,
        Language $fr,
        Language $it
    ) {
        $supported = [$en, $fr, $it];

        $em->getRepository(Language::class)->willReturn($language_repo);
        $language_repo->getPortalLanguages()->willReturn($supported);

        $this->getEnabledLanguages()->shouldReturn($supported);
    }

    public function it_gives_you_a_translator_object_for_the_active_lang_on_language_stack(
        Translate $translate,
        LanguageStack $language_stack,
        Language $en
    ) {
        $language_stack->getActive()->willReturn($en);

        $translate->setLanguage($en)->shouldBeCalled();
        $this->getTranslator();
    }

    public function it_gives_you_a_translator_object_for_default_lang_if_nothing_in_stack(
        Translate $translate,
        LanguageStack $language_stack,
        Language $en
    ) {
        $language_stack->getActive()->willReturn(null);
        $language_stack->getDefaultLanguage()->willReturn($en);

        $translate->setLanguage($en)->shouldBeCalled();
        $this->getTranslator();
    }

    public function it_gets_you_a_translator_for_the_specific_lang_you_give_it(
        Translate $translate,
        Language $en
    ) {
        $translate->setLanguage($en)->shouldBeCalled();
        $this->getTranslator($en);
    }
}
