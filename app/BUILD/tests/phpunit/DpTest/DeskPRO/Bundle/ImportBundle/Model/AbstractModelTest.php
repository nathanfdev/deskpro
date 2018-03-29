<?php

namespace DpTest\DeskPRO\Bundle\ImportBundle\Model;

use DeskPRO\Bundle\ImportBundle\Model\PrimaryImportModelInterface;
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
