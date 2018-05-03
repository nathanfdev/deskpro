<?php

namespace DeskPRO\Bundle\UpdateBundle\Distro\Manifest;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DistroRelease
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var array
     */
    private $flags;

    /**
     * @var string
     */
    private $detailUrl;

    /**
     * @var string
     */
    private $zipUrl;

    /**
     * @var int
     */
    private $filesize;

    /**
     * @var string
     */
    private $sha256;

    /**
     * @param array $props
     */
    public function __construct(array $props)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        // whitelist all unknown keys, they might be new
        foreach (array_keys($props) as $k) {
            if (!$resolver->isDefined($k)) {
                $resolver->setDefined($k);
            }
        }

        $props = $resolver->resolve($props);

        $this->id        = $props['id'];
        $this->name      = $props['name'];
        $this->date      = $props['date'];
        $this->detailUrl = $props['detail_url'];
        $this->zipUrl    = $props['zip_url'];
        $this->filesize  = $props['filesize'];
        $this->sha256    = $props['checksums']['sha256'];
        $this->flags     = $props['flags'];
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'id', 'name', 'date', 'detail_url',
            'zip_url', 'filesize', 'checksums',

            // and we dont use this at the moment:
            'commit', 'track',
        ]);
        $resolver->setDefault('flags', []);
        $resolver->setDefault('name', null);
        $resolver->setAllowedValues('checksums', function ($v) {
            return is_array($v) && !empty($v['sha256']);
        });
        $resolver->setNormalizer('date', function (Options $options, $v) {
            if ($v instanceof \DateTime) {
                return $v;
            }

            return \DateTime::createFromFormat('Y-m-d H:i:s', $v, new \DateTimeZone('UTC'));
        });
        $resolver->setNormalizer('name', function (Options $options, $v) {
            return $v ?: $options['id'];
        });
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getDetailUrl()
    {
        return $this->detailUrl;
    }

    /**
     * @return string
     */
    public function getZipUrl()
    {
        return $this->zipUrl;
    }

    /**
     * @return int
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

    /**
     * @return array
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @param string $flagId
     *
     * @return bool
     */
    public function hasFlag($flagId)
    {
        return in_array($flagId, $this->flags);
    }

    /**
     * @return string
     */
    public function getSha256()
    {
        return $this->sha256;
    }
}
