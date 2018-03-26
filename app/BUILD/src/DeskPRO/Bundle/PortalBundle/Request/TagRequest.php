<?php

namespace DeskPRO\Bundle\PortalBundle\Request;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagRequest extends SymfonyRequest
{
    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolver
     */
    protected $optionsResolver;

    /**
     * @return \Symfony\Component\OptionsResolver\OptionsResolver
     */
    public function getOptionsResolver()
    {
        return $this->optionsResolver;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $optionsResolver
     */
    public function setOptionsResolver(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefined('_tag_name');
        $this->optionsResolver = $optionsResolver;
    }

    /**
     * @param array $collectAttr Array of attributes to fetch options from a parent request
     *
     * @return array
     */
    public function getTagOptions(array $collectAttr = [])
    {
        $opts = $this->query->get('tag_options', []);

        if ($collectAttr) {
            foreach ($collectAttr as $attr) {
                if ($this->attributes->has($attr)) {
                    $v = $this->attributes->get($attr);
                    if (is_array($v)) {
                        $opts = array_merge($v, $opts);
                    }
                }
            }
        }

        return $this->getOptionsResolver()->resolve($opts);
    }
}
