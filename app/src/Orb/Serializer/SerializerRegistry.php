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

/**
 * DeskPRO
 *
 * @package Orb
 * @subpackage Serializer
 */

namespace Orb\Serializer;

class SerializerRegistry implements SerializerInterface
{
	/**
	 * @var SerializerInterface[]
	 */
	private $serializers;

	/**
	 * @param array $serializers start the registry off with some serializers
	 */
	public function __construct(array $serializers = array())
	{
		foreach ($serializers as $serializer) {
			$this->addSerializer($serializer);
		}
	}

	/**
	 * @param mixed  $data   anything that the serializer can handle
	 * @param string $view   defaults to "default" but can be anything and the handlers understand what to do
	 * @param string $format requested return format - defaults to an array
	 * @throws \InvalidArgumentException if the passed data isn't supported
	 * @return mixed
	 */
	public function serialize($data, $view = 'default', $format = 'array')
	{
		foreach ($this->serializers as $serializer) {
			if ($serializer->supports($data, $view, $format)) {
				return $serializer->serialize($data, $view, $format);
			}
		}

		throw new \InvalidArgumentException('cannot serialize data');
	}


	/**
	 * @param mixed  $data   anything that the serializer can handle
	 * @param string $view   defaults to "default" but can be anything and the handlers understand what to do
	 * @param string $format requested return format - defaults to an array
	 * @return mixed
	 */
	public function supports($data, $view = 'default', $format = 'array')
	{
		foreach ($this->serializers as $serializer) {
			if ($serializer->supports($data, $view, $format)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add a serializer
	 *
	 * @param SerializerInterface $serializer
	 */
	public function addSerializer(SerializerInterface $serializer)
	{
		$this->serializers[] = $serializer;
	}


}
