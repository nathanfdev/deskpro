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
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Language\LanguageStack
 */
class LanguageStackSpec extends ObjectBehavior
{
    public function let(
        SettingsResolver $settings_resolver,
        EntityManager $em
    ) {
        $this->beConstructedWith($settings_resolver, $em);
    }

    public function it_will_return_null_if_no_active_language_set()
    {
        $this->getActive()->shouldReturn(null);
    }

    public function it_returns_active_language_when_one_on_stack(Language $lang)
    {
        $lang->getId()->willReturn(1);
        $this->push($lang);
        $this->getActive()->shouldReturn($lang);
    }

    public function it_returns_active_language_when_multiple_on_stack(
        Language $lang,
        Language $lang2
    ) {
        $lang->getId()->willReturn(1);
        $lang2->getId()->willReturn(2);
        $this->push($lang);
        $this->push($lang2);
        $this->getActive()->shouldReturn($lang2);
    }

    public function it_allows_you_to_pop_the_active_lang_off_the_stack(
        Language $lang,
        Language $lang2,
        Language $lang3
    ) {
        $lang->getId()->willReturn(1);
        $lang2->getId()->willReturn(2);
        $lang3->getId()->willReturn(3);
        $this->push($lang);
        $this->push($lang2);
        $this->push($lang3);
        $this->pop();
        $this->getActive()->shouldReturn($lang2);
    }

    public function it_look_first_at_settings_for_default_language(
        SettingsResolver $settings_resolver,
        SettingsBag $settings_bag,
        Language $default_lang,
        EntityManager $em,
        LanguageRepo $language_repo
    ) {
        $settings_resolver->getGlobalSettings()->willReturn($settings_bag);
        $settings_bag->get('core.default_language_id')->willReturn(5);

        $em->getRepository(Language::class)->willReturn($language_repo);
        $language_repo->find(5)->willReturn($default_lang);

        $this->getDefaultLanguage()->shouldReturn($default_lang);
    }

    public function it_will_default_to_lang_id_1_if_not_found_via_settings(
        SettingsResolver $settings_resolver,
        SettingsBag $settings_bag,
        Language $default_lang,
        EntityManager $em,
        LanguageRepo $language_repo
    ) {
        $settings_resolver->getGlobalSettings()->willReturn($settings_bag);
        $settings_bag->get('core.default_language_id')->willReturn(null);

        $em->getRepository(Language::class)->willReturn($language_repo);
        $language_repo->find(1)->willReturn($default_lang);

        $this->getDefaultLanguage()->shouldReturn($default_lang);
    }

    public function it_allows_you_a_shortcut_to_easily_push_default_lang(
        SettingsResolver $settings_resolver,
        SettingsBag $settings_bag,
        Language $default_lang,
        EntityManager $em,
        LanguageRepo $language_repo
    ) {
        $settings_resolver->getGlobalSettings()->willReturn($settings_bag);
        $settings_bag->get('core.default_language_id')->willReturn(null);

        $em->getRepository(Language::class)->willReturn($language_repo);
        $language_repo->find(1)->willReturn($default_lang);

        $this->pushDefault();
        $this->getActive()->shouldReturn($default_lang);
    }
}
