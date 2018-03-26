<?php

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
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\HitRecordRepository")
 * @ORM\Table(name="hit_record", indexes={
 *     @ORM\Index(name="visitor_id_idx", columns={"visitor_id"}),
 *     @ORM\Index(name="page_type_idx", columns={"page_type", "page_id"})
 * })
 * @ORM\ChangeTrackingPolicy("NOTIFY")
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
        $this->setModelField('page_type', $page_type);
        $this->setModelField('page_id', $page_id);
        $this->setModelField('url', $url);
        $this->setModelField('meta', $meta);
        $this->setModelField('date_created', new \DateTime());
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
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->setModelField('id', $id);

        return $this;
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
     *
     * @return $this
     */
    public function setVisitorId($visitor_id)
    {
        $this->setModelField('visitor_id', $visitor_id);

        return $this;
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
     *
     * @return $this
     */
    public function setIpAddress($ip_address)
    {
        $this->setModelField('ip_address', $ip_address);

        return $this;
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
     *
     * @return $this
     */
    public function setPageType($page_type)
    {
        $this->setModelField('page_type', $page_type);

        return $this;
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
     *
     * @return $this
     */
    public function setPageId($page_id)
    {
        $this->setModelField('page_id', $page_id);

        return $this;
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
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->setModelField('url', $url);

        return $this;
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
     *
     * @return $this
     */
    public function setReferrer($referrer)
    {
        $this->setModelField('referrer', $referrer);

        return $this;
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
     *
     * @return $this
     */
    public function setGeoCountry($geo_country)
    {
        $this->setModelField('geo_country', $geo_country);

        return $this;
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
     *
     * @return $this
     */
    public function setUserAgent($user_agent)
    {
        $this->setModelField('user_agent', $user_agent);

        return $this;
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
     *
     * @return $this
     */
    public function setMeta($meta)
    {
        $this->setModelField('meta', $meta ?: null);

        return $this;
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
     *
     * @return $this
     */
    public function setDateCreated($date_created)
    {
        $this->setModelField('date_created', $date_created);

        return $this;
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
