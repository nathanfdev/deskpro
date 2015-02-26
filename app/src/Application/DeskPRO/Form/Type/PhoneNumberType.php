<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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



namespace Application\DeskPRO\Form\Type;


use Application\DeskPRO\Form\Transformer\PhoneNumberModelTransformer;
use Orb\Util\PhoneNumbers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;

class PhoneNumberType extends AbstractType
{
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('number', 'text', array(
			'required' => false,
			'label' => false,
			'attr' => array(
				'placeholder' => '+19021111111',
				'class' => 'phone_number',
			),
			'constraints' => array(
				new NotBlank(array('message' => 'Phone number is invalid.')),
			),
		));
		$builder->get('number')->addModelTransformer(new PhoneNumberModelTransformer());

		$builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event){
			$data = $event->getForm()->getData();

			/**
			 * moved from PhoneNumber entity:
			 * We do logic here (with the help of Google's libphonenumber) to
			 * get the region code, and validate/format the number.
			 */

			if ($data && $data['number']) {
				$number = $data['number'];
				$data['region'] = PhoneNumbers::getRegionForNumber($number);
				$data['guessed_type'] = PhoneNumbers::getTypeCode($number);
			}
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Application\DeskPRO\Entity\PhoneNumber',
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'phone_number';
	}
}