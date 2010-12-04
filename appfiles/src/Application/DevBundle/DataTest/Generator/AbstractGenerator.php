<?php

namespace Application\DevBundle\DataTest\Generator;

abstract class AbstractGenerator
{
	/**
	 * @var Application\DevBundle\DataSet\DataSet
	 */
	protected $dataset;

	/**
	 * @var Application\DevBundle\DataTest\Schema\AbstractSchema
	 */
	protected $schema;

	protected $cached_choice_array = array();

	public function __construct(\Application\DevBundle\DataTest\DataSet\Basic $dataset)
	{
		$this->dataset = $dataset;
	}


	
	/**
	 * Fill the database with data
	 */
	abstract public function run(\DeskPRO\DBAL\Connection $db, \Symfony\Component\Console\Output\Output $output);



	public function chooseFromChanceArray(array $choices, $cache_name = null)
	{
		if ($cache_name !== null AND isset($this->cached_choice_array[$cache_name])) {
			$filled_choices = $this->cached_choice_array[$cache_name];
		} else {
			$filled_choices = array();
			foreach ($choices as $k => $info) {
				$filled_choices = array_merge($filled_choices, array_fill(0, $info[0], $k));
			}

			if ($cache_name !== null) {
				$this->cached_choice_array[$cache_name] = $filled_choices;
			}
		}

		$choice_index = array_rand($filled_choices);
		if (!is_array($filled_choices)) echo $cache_name;
		$choice_index = $filled_choices[$choice_index];

		$choice = $choices[$choice_index][1];

		return $choice;
	}



	/**
	 * @return \DateTime
	 */
	public function chooseDateFromChanceArray(array $date_choices, $cache_name = null)
	{
		$choice = $this->chooseFromChanceArray($date_choices, $cache_name);
		$timestamp = mt_rand($choice[0], $choice[1]);

		$datetime = new \DateTime("@$timestamp");

		return $datetime;
	}


	
	public function buildSchema(\DeskPRO\DBAL\Connection $super_db, \Symfony\Component\Console\Output\Output $output)
	{
		$output->write("<comment>\nBUILDING SCHEMA\n</comment>\n");
		
		$schema = $this->getSchema();
		$schema->buildSchema($super_db, $output);
	}


	/**
	 * @return Application\DevBundle\DataTest\Schema\AbstractSchema
	 */
	public function getSchema()
	{
		if ($this->schema !== null) return $this->schema;

		$classname = $this->getSchemaClassname();
		$this->schema = new $classname();

		return $this->schema;
	}

	

	/**
	 * Get the name of the schema class that creates the db
	 * @return string
	 */
	public function getSchemaClassname()
	{
		$name_parts = explode('\\', get_class($this));
		$name = array_pop($name_parts);

		return "Application\\DevBundle\\DataTest\\Schema\\$name";
	}
}