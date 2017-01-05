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

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\SavedForm;

use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\SavedForm\SavedFormView
 */
class SavedFormViewSpec extends ObjectBehavior
{
    public function it_can_generate_an_array_of_key_val_to_submit_in_a_form(
        SavedForm $saved_form
    ) {
        $saved_form->getFormData()->willReturn(
            [
                'ticket' => [
                    'department' => 2,
                    'subject'    => 'my ticket subject',
                    'message'    => [
                        'message' => 'my ticket message',
                    ],
                    'person' => [
                        'user_email' => [
                            'email' => 'chris.tickner@deskpro.com',
                        ],
                    ],
                    'submit'         => '',
                    '_dp_csrf_token' => '34dadfa',
                ],
            ]
        );

        $this->beConstructedWith($saved_form);

        $this->getFields()->shouldReturn(
            [
                'ticket[department]'                => 2,
                'ticket[subject]'                   => 'my ticket subject',
                'ticket[message][message]'          => 'my ticket message',
                'ticket[person][user_email][email]' => 'chris.tickner@deskpro.com',
                'ticket[submit]'                    => '',
                'ticket[_dp_csrf_token]'            => '', // NOTE: _dp_csrf_token's value must be empty when rendering
            ]
        );
    }

    public function it_gets_route_info(
        SavedForm $saved_form
    ) {
        $saved_form->getMetaData()->willReturn([
            'route'        => 'portal_new_ticket',
            'route_params' => ['some' => 'params'],
        ]);

        $this->beConstructedWith($saved_form);

        $this->getRouteName()->shouldBe('portal_new_ticket');
        $this->getRouteParams()->shouldBe(['some' => 'params']);
    }

    public function it_always_says_post_is_the_form_method(
        SavedForm $saved_form
    ) {
        $this->beConstructedWith($saved_form);

        $this->getMethod()->shouldBe('POST');
    }
}
