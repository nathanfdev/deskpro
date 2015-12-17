<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */
namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="hit_record", indexes={
 *     @ORM\Index(name="visitor_id_idx", columns={"visitor_id"}),
 *     @ORM\Index(name="page_type_idx", columns={"page_type", "page_id"})
 * })
 */
class HitRecord implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const PAGETYPE_NEWS     = 'deskpro.news_view';
    const PAGETYPE_ARTICLE  = 'deskpro.kb_view';
    const PAGETYPE_FEEDBACK = 'deskpro.feedback_view';
    const PAGETYPE_DOWNLOAD = 'deskpro.download_view';

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    protected $visitor_id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $ip_address = '';

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull()
     */
    protected $page_type;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull()
     */
    protected $page_id = '';

    /**
     * @ORM\Column(type="string", length=1000)
     * @Assert\NotNull()
     */
    protected $url;

    /**
     * @ORM\Column(type="string", length=1000)
     * @Assert\NotNull()
     */
    protected $referrer = '';

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull()
     */
    protected $user_agent = '';

    /**
     * @ORM\Column(type="string", length=8)
     * @Assert\NotNull()
     */
    protected $geo_country = '';

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $meta;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\NotNull()
     */
    protected $date_created;

    /**
     * HitRecord constructor.
     *
     * @param string     $page_type
     * @param string     $page_id
     * @param string     $url
     * @param array|null $meta
     */
    public function __construct($page_type, $page_id, $url, array $meta = null)
    {
        $this->page_type    = $page_type;
        $this->page_id      = $page_id;
        $this->url          = $url;
        $this->meta         = $meta;
        $this->date_created = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getVisitorId()
    {
        return $this->visitor_id;
    }

    /**
     * @param mixed $visitor_id
     */
    public function setVisitorId($visitor_id)
    {
        $this->visitor_id = $visitor_id;
    }

    /**
     * @return mixed
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * @param mixed $ip_address
     */
    public function setIpAddress($ip_address)
    {
        $this->ip_address = $ip_address;
    }

    /**
     * @return mixed
     */
    public function getPageType()
    {
        return $this->page_type;
    }

    /**
     * @param mixed $page_type
     */
    public function setPageType($page_type)
    {
        $this->page_type = $page_type;
    }

    /**
     * @return mixed
     */
    public function getPageId()
    {
        return $this->page_id;
    }

    /**
     * @param mixed $page_id
     */
    public function setPageId($page_id)
    {
        $this->page_id = $page_id;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * @param mixed $referrer
     */
    public function setReferrer($referrer)
    {
        $this->referrer = $referrer;
    }

    /**
     * @return mixed
     */
    public function getGeoCountry()
    {
        return $this->geo_country;
    }

    /**
     * @param mixed $geo_country
     */
    public function setGeoCountry($geo_country)
    {
        $this->geo_country = $geo_country;
    }

    /**
     * @return mixed
     */
    public function getUserAgent()
    {
        return $this->user_agent;
    }

    /**
     * @param mixed $user_agent
     */
    public function setUserAgent($user_agent)
    {
        $this->user_agent = $user_agent;
    }

    /**
     * @return mixed
     */
    public function getMeta()
    {
        return $this->meta ?: [];
    }

    /**
     * @param mixed $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta ?: null;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param \DateTime $date_created
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
    }

    /**
     * An array of the types of pages that DeskPRO itself knows about
     * as single content pages.
     *
     * @return array
     */
    public static function getContentPageTypes()
    {
        return [
            self::PAGETYPE_NEWS,
            self::PAGETYPE_ARTICLE,
            self::PAGETYPE_DOWNLOAD,
            self::PAGETYPE_FEEDBACK,
        ];
    }
}
