<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Encryption\Form\Type;

use Application\DeskPRO\Encryption\DpEnc;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class DpEncTextType extends TextType
{
    /**
     * @var DpEnc
     */
    private $enc;

    /**
     * @param DpEnc $enc
     */
    public function __construct(DpEnc $enc)
    {
        $this->enc = $enc;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $enc = $this->enc;

        $builder->addModelTransformer(new CallbackTransformer(
            function ($orig) use ($enc) {
                return $enc->dpEncrypt($orig);
            },
            function ($saved) use ($enc) {
                return $enc->dpDecrypt($saved);
            }
        ));
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'dp_enc_text';
    }
}
