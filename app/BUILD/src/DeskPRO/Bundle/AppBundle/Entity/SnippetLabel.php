<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Labels\Label;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SnippetLabel.
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\SnippetLabelRepository")
 * @ORM\Table(name="snippet_labels", indexes={@ORM\Index(name="label", columns={"label"})})
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 */
class SnippetLabel implements EntityInterface, NotifyPropertyChanged, Label
{
    use NotifyPropertyChangedTrait;

    /**
     * The 'type' of label this is for, as it could be found in the
     * LabelDef.
     */
    const LABEL_TYPENAME = 'snippet';

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Snippet")
     * @ORM\JoinColumn(name="snippet_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var Snippet
     */
    protected $snippet;

    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255, name="label")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $label;

    public function __construct($value = null)
    {
        if ($value) {
            $this->setLabel($value);
        }
    }

    /**
     * Composite id (person_id + name).
     *
     * return array
     */
    public function getId()
    {
        return [$this->snippet->getId(), $this->label];
    }

    /**
     * @return Snippet
     */
    public function getSnippet()
    {
        return $this->snippet;
    }

    /**
     * @param Snippet $snippet
     *
     * @return SnippetLabel
     */
    public function setSnippet($snippet)
    {
        $this->setModelField('snippet', $snippet);

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return SnippetLabel
     */
    public function setLabel($label)
    {
        $this->setModelField('label', $label);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return static::LABEL_TYPENAME;
    }
}
