<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

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
	 * @var array
	 */
	protected $toAdd = array();
	/**
	 * @var array
	 */
	protected $toDelete = array();

	public static function getSubscribedEvents()
	{
		return array_merge(parent::getSubscribedEvents(), array(
			FormEvents::POST_SUBMIT => 'postSubmit',
		));
	}

	/**
	 * @param FormEvent $event
	 * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
	 */
	public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
	    $this->toAdd = array();
	    $this->toDelete = array();

        if (null === $data || '' === $data) {
            $data = array();
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

	    $map = array();
	    foreach ($form as $name => $child) {
		    $map[$child->get('id')->getData()] = $name;
	    }

	    $newData = array();
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
			    if (!isset($data[$name])) {
				    $this->toDelete[] = $child->getData();
				    $form->remove($name);
			    }
		    }
	    }

	    // Add all additional rows
	    if ($this->allowAdd) {
		    foreach ($data as $name => $value) {
			    if (!$form->has($name)) {
				    $form->add($name, $this->type, array_replace(array(
					    'property_path' => '['.$name.']',
				    ), $this->options));

				    // we add only item index here
				    $this->toAdd[] = $name;
			    }
		    }
	    }
    }

	/**
	 * @param FormEvent $event
	 */
	public function postSubmit(FormEvent $event)
	{
		$toAdd = array();
		$form = $event->getForm();

		foreach ($this->toAdd as $addIndex) {
			$toAdd[] = $form[$addIndex]->getData();
		}

		$this->toAdd = $toAdd;
	}

	/**
	 * set entities managed by EM
	 * @param FormEvent $event
	 */
	public function manageEntities(FormEvent $event)
	{
		$data = $event->getData();
		if (empty($data['em']) || !$data['em'] instanceof EntityManager) {
			return;
		}

		foreach ($this->toAdd as $add) {
			if ($add instanceof DomainObject) {
				$data['em']->persist($add);
			}
		}

		foreach ($this->toDelete as $del) {
			if ($del instanceof DomainObject) {
				$data['em']->remove($del);
			}
		}

		$this->toAdd = array();
		$this->toDelete = array();
	}
}
