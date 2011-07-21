<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A single table that controls subscriptions to all common content types
 * 
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\ContentSubscription")
 * @orm:Table(name="content_subscriptions")
 */
class ContentSubscription extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person;

	/**
	 * Enable email notifications for the subscription
	 *
	 * @var bool
	 * @orm:Column(name="use_email", type="boolean")
	 */
	protected $use_email = false;

	/**
	 * The last time the user dismissed a notice about this sub
	 *
	 * @var \DateTime
	 * @orm:Column(name="last_dismiss_date",type="datetime")
	 */
	protected $last_dismiss_date;

	/**
	 * The last time we emailed the user about this sub
	 *
	 * @var \DateTime
	 * @orm:Column(name="last_email_date",type="datetime")
	 */
	protected $last_email_date;

	/**
	 * The last time the subscription was updated.
	 * 
	 * @var \DateTime
	 * @orm:Column(name="updated_date",type="datetime")
	 */
	protected $updated_date;

	/**
	 * @var \Application\DeskPRO\Entity\Article
	 * @orm:ManyToOne(targetEntity="Article", fetch="EAGER")
	 * @orm:JoinColumn(name="article_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $article = null;

	/**
	 * @var \Application\DeskPRO\Entity\Download
	 * @orm:ManyToOne(targetEntity="Download", fetch="EAGER")
	 * @orm:JoinColumn(name="download_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $download = null;

	/**
	 * @var \Application\DeskPRO\Entity\Idea
	 * @orm:ManyToOne(targetEntity="Idea", fetch="EAGER")
	 * @orm:JoinColumn(name="idea_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $idea = null;

	/**
	 * @var \Application\DeskPRO\Entity\News
	 * @orm:ManyToOne(targetEntity="News", fetch="EAGER")
	 * @orm:JoinColumn(name="news_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $news = null;

	/**
	 * @param $content_object
	 * @param $person
	 * @return \Application\DeskPRO\Entity\ContentSubscription
	 */
	public static function create($content_object, $person)
	{
		$sub = new self();
		$sub->person = $person;

		if ($content_object instanceof Article) {
			$sub->article = $content_object;
		} elseif ($content_object instanceof Download) {
			$sub->download = $content_object;
		} elseif ($content_object instanceof News) {
			$sub->news = $content_object;
		} elseif ($content_object instanceof Idea) {
			$sub->idea = $content_object;
		} else {
			throw new \InvalidArgumentException("\$content_object must be Article, Download, News or Idea. Got `" . get_class($content_object) . "`");
		}

		return $sub;
	}

	public function __construct()
	{
		$this->last_dismiss_date  = new \DateTime();
		$this->last_email_date    = new \DateTime();
		$this->updated_date       = new \DateTime();
	}


	/**
	 * "touch"es this subscription to update the last_X_date's, so whatever notifications
	 * are involved are reset.
	 * 
	 * @return void
	 */
	public function touch()
	{
		$this->last_dismiss_date = new \DateTime();
		$this->last_email_date = new \DateTime();
	}
}