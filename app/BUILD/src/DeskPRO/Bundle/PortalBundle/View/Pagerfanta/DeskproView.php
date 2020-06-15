<?php



namespace DeskPRO\Bundle\PortalBundle\View\Pagerfanta;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Pagerfanta\View\DefaultView;
use Pagerfanta\View\Template\TemplateInterface;

class DeskproView extends DefaultView
{
    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * DeskproView constructor.
     *
     * @param TemplateInterface|null $template
     * @param LanguageManager|null $languageManager
     */
    public function __construct(TemplateInterface $template = null, LanguageManager $languageManager = null)
    {
        $this->languageManager = $languageManager;

        parent::__construct($template);
    }

    /**
     * @return Template\DeskproTemplate
     */
    protected function createDefaultTemplate()
    {
        return new Template\DeskproTemplate($this->languageManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultProximity()
    {
        return 3;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twitter_bootstrap';
    }
}
