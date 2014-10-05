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

namespace Application\DeskPRO\Form\Type\CustomFields;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContextualChoiceType extends ChoiceType
{
	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		parent::setDefaultOptions($resolver);
		$resolver->setRequired(array('context'));
	}

	/**
	 * @param EntityRepository $er
	 * @param Options $options
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getChoicesQueryBuilder(EntityRepository $er, Options $options)
	{
		/** @var DomainObject $ctx */
		$ctx = $options['context'];
		$def = $this->definition;

		return $er->createQueryBuilder('d')
			->select('d.id, d.title')
			->where('d.parent = :parent')
			->andWhere('d.owner_class = :owner_class and d.context_class = :cc and d.context_id = :cid')
			->setParameter('parent', $def['id'])
			->setParameter('owner_class', $def['owner_class'])
			->setParameter('cc', get_class($ctx))
			->setParameter('cid', $ctx['id']);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'cf_contextual_choice';
	}
}
