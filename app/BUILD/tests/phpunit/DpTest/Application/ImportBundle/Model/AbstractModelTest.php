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

namespace DpTest\Application\ImportBundle\Model;

use Application\ImportBundle\Model\PrimaryImportModelInterface;
use DpTest\ApiTestCase;
use JMS\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class AbstractModelTest.
 */
abstract class AbstractModelTest extends ApiTestCase
{
    protected static $modelClass;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->serializer = $this->get('serializer');
        $this->validator  = $this->get('validator');
    }

    /**
     * @param array $data
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface|ConstraintViolation[]
     */
    protected function validateData(array $data)
    {
        return $this->validator->validate($this->getModel($data));
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function transformData(array $data)
    {
        $model  = $this->getModel($data);
        $errors = $this->validator->validate($model);
        if (count($errors)) {
            throw new \Exception('Unable to transform model, validation failed');
        }

        return $this->serializer->toArray($model);
    }

    /**
     * @param array $data
     *
     * @return PrimaryImportModelInterface
     */
    protected function getModel(array $data)
    {
        /** @var PrimaryImportModelInterface $model */
        $model = $this->serializer->fromArray($data, static::$modelClass);
        $model->setOid(1);
        $model->setRawData($data);

        return $model;
    }
}
