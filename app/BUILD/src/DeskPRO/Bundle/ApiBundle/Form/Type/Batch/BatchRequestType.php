<?php

namespace DeskPRO\Bundle\ApiBundle\Form\Type\Batch;

use DeskPRO\Bundle\AppBundle\Form\Type\JsonArrayType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BatchRequestType.
 */
class BatchRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', TextType::class, [
                'required' => true,
            ])
            ->add('method', ChoiceType::class, [
                'required'          => false,
                'empty_data'        => Request::METHOD_GET,
                'choices_as_values' => true,
                'choices'           => [
                    Request::METHOD_GET,
                    Request::METHOD_POST,
                    Request::METHOD_PUT,
                    Request::METHOD_DELETE,
                ],
            ])
            ->add('data', JsonArrayType::class, [
                'required'   => false,
            ])
            ->add('headers', JsonArrayType::class, [
                'required'   => false,
            ])
            ->add('params', JsonArrayType::class, [
                'required'   => false,
                'empty_data' => [],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (is_string($data)) {
            $data = ['url' => $data];
        }

        // verify sub request url
        if (strpos($data['url'], '/api/v2') !== 0) {
            $data['url'] = '/api/v2/'.ltrim($data['url'], '/');
        }

        $event->setData($data);
    }
}
