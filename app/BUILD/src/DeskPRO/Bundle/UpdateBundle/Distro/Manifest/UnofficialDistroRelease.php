<?php

namespace DeskPRO\Bundle\UpdateBundle\Distro\Manifest;

use Symfony\Component\OptionsResolver\OptionsResolver;

class UnofficialDistroRelease extends DistroRelease
{
    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'id'         => 'BUILD',
            'detail_url' => 'https://deskpro.github.io/releases/info/unknown-release.html',
            'zip_url'    => 'https://deskpro.github.io/releases/info/unknown-release.html',
            'date'       => new \DateTime(),
            'filesize'   => 0,
            'checksums'  => [
                'sha256' => hash('sha256', ''),
            ],
            'commit' => 'unknown',
            'track'  => 'stable',
        ]);
    }
}
