<?php

namespace Application\DeskPRO\Form\EventListener;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener as BaseListener;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ResizeFormListener extends BaseListener
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $newEntriesMap;

    public function __construct($type, array $options, $allowAdd, $allowDelete, $deleteEmpty, EntityManager $em)
    {
        parent::__construct($type, $options, $allowAdd, $allowDelete, $deleteEmpty);
        $this->em = $em;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array_merge(parent::getSubscribedEvents(), [
            FormEvents::POST_SUBMIT => 'postSubmit',
        ]);
    }

    /**
     * @param FormEvent $event
     *
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function preSubmit(FormEvent $event)
    {
        $form                = $event->getForm();
        $data                = $event->getData();
        $this->newEntriesMap = [];

        if (null === $data || '' === $data) {
            $data = [];
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        $map = [];
        foreach ($form as $name => $child) {
            $map[$child->get('id')->getData()] = $name;
        }

        $newData = [];
        foreach ($data as $value) {
            if (isset($map[$value['id']])) {
                $newData[$map[$value['id']]] = $value;
            }
        }
        foreach ($data as $value) {
            if (!isset($map[$value['id']])) {
                $newData[$value['id']] = $value;
            }
        }
        $data = $newData;
        $event->setData($data);

        // Remove all empty rows
        if ($this->allowDelete) {
            foreach ($form as $name => $child) {
                // todo $data[$name]['title'] is very rare! only for DpCategoryBuilderType
                if (!isset($data[$name]) || empty($data[$name]['title']) && $child->getData() instanceof DomainObject) {
                    $this->em->remove($child->getData());
                    $form->remove($name);
                }
            }
        }

        // Add all additional rows
        if ($this->allowAdd) {
            foreach ($data as $name => $value) {
                // todo: very strange issue. might be php bug
                if (!$name) {
                    continue;
                }

                // todo $value['title'] is very rare! only for DpCategoryBuilderType
                if (!$form->has($name) && !empty($value['title'])) {
                    $form->add($name, $this->type, array_replace([
                        'property_path' => '['.$name.']',
                    ], $this->options));

                    // we add only item index here
                    $this->newEntriesMap[] = $name;
                }
            }
        }
    }

    /**
     * re-map.
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        foreach ($this->newEntriesMap as $name) {
            if ($form[$name]->getData() instanceof DomainObject) {
                $this->em->persist($form[$name]->getData());
            }
        }

        $this->newEntriesMap = [];
    }
}
