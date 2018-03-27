<?php

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
