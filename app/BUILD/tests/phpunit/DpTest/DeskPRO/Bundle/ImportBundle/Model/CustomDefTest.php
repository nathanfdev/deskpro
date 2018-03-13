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

namespace DpTest\DeskPRO\Bundle\ImportBundle\Model;

use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class CustomDefTest.
 */
class CustomDefTest extends AbstractModelTest
{
    private $customDefTypes = [
        Model\PersonCustomDef::class,
        Model\TicketCustomDef::class,
        Model\ArticleCustomDef::class,
        Model\OrganizationCustomDef::class,
        Model\FeedbackCustomDef::class,
        Model\ChatCustomDef::class,
    ];

    /**
     * @dataProvider simpleDefTypeProvider
     *
     * @param string $modelClass
     * @param string $widgetType
     */
    public function test_simple_field($modelClass, $widgetType)
    {
        $params = [
            'title'       => 'Custom Def',
            'widget_type' => $widgetType,
        ];

        static::$modelClass = $modelClass;
        $this->assertEquals($this->transformData($params), array_merge($params, [
            'description'     => '',
            'is_enabled'      => true,
            'is_user_enabled' => true,
            'is_agent_field'  => true,
            'options'         => [],
            'choices'         => [],
        ]));
    }

    /**
     * @dataProvider choiceDefTypeProvider
     *
     * @param string $modelClass
     * @param string $widgetType
     */
    public function test_choice_field($modelClass, $widgetType)
    {
        $params = [
            'title'       => 'Custom Def',
            'widget_type' => $widgetType,
            'choices'     => [
                [
                    'title'   => 'Choice 1',
                    'choices' => [
                        [
                            'title'   => 'Choice 1a',
                            'choices' => [],
                        ],
                        [
                            'title'   => 'Choice 1b',
                            'choices' => [],
                        ],
                    ],
                ],
            ],
        ];

        static::$modelClass = $modelClass;
        $this->assertEquals($this->transformData($params), array_merge($params, [
            'description'     => '',
            'is_enabled'      => true,
            'is_user_enabled' => true,
            'is_agent_field'  => true,
            'options'         => [],
        ]));
    }

    /**
     * @dataProvider unknownDefTypeProvider
     *
     * @param string $modelClass
     * @param string $widgetType
     */
    public function test_unknown_field($modelClass, $widgetType)
    {
        $params = [
            'title'       => 'Custom Def',
            'widget_type' => $widgetType,
        ];

        static::$modelClass = $modelClass;
        $errors             = $this->validateData($params);
        $this->assertEquals('widgetType', $errors[0]->getPropertyPath());
    }

    /**
     * @return array
     */
    public function simpleDefTypeProvider()
    {
        $args = [];
        foreach (['text', 'textarea', 'hidden', 'display', 'date', 'datetime', 'toggle'] as $widgetType) {
            foreach ($this->customDefTypes as $modelClass) {
                $args[] = [$modelClass, $widgetType];
            }
        }

        return $args;
    }

    /**
     * @return array
     */
    public function choiceDefTypeProvider()
    {
        $args = [];
        foreach (['choice', 'multichoice', 'radio', 'checkbox'] as $widgetType) {
            foreach ($this->customDefTypes as $modelClass) {
                $args[] = [$modelClass, $widgetType];
            }
        }

        return $args;
    }

    /**
     * @return array
     */
    public function unknownDefTypeProvider()
    {
        $args = [];
        foreach (['unkwnown', '', 'text_field'] as $widgetType) {
            foreach ($this->customDefTypes as $modelClass) {
                $args[] = [$modelClass, $widgetType];
            }
        }

        return $args;
    }
}
