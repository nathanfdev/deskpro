<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Model;

use DeskPRO\Bundle\ImportBundle\Model\Setting;

/**
 * Class SettingTest.
 */
class SettingTest extends AbstractModelTest
{
    protected static $modelClass = Setting::class;

    public function test_required_params_validation()
    {
        $errors = $this->validateData([]);

        $this->assertCount(3, $errors);
        $this->assertEquals('name', $errors[0]->getPropertyPath());
        $this->assertEquals('value', $errors[1]->getPropertyPath());
        $this->assertEquals('raw_data', $errors[2]->getPropertyPath());
    }

    /**
     * @dataProvider paramsProvider
     *
     * @param mixed $value
     */
    public function test_params($value)
    {
        $params = [
            'name'  => 'setting_name',
            'value' => $value,
        ];

        $this->assertEquals($this->transformData($params), $params);
    }

    /**
     * @return array
     */
    public function paramsProvider()
    {
        return [
            [''],
            [1],
            ['string'],
            [true],
            [false],
            [0],
        ];
    }
}
