<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SendmailBundle\Templating\Templates;

use Application\DeskPRO\Entity\Template as TemplateEntity;
use Application\DeskPRO\Templating\EmailTemplatesDesc;
use Application\DeskPRO\Templating\Templates\EmailTemplateCode;
use Application\DeskPRO\Templating\Templates\Template;
use Application\DeskPRO\Templating\Templates\TemplateCustom;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\SendmailBundle\Twig\PreProcessor\EmailPreProcessor;
use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use Orb\Util\Strings;
use Twig_Environment;

class TemplateSet
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @param EntityManager    $em
     * @param Twig_Environment $twig
     */
    public function __construct(EntityManager $em, Twig_Environment $twig)
    {
        $this->em   = $em;
        $this->twig = $twig;
    }

    /**
     * @param string $name
     *
     * @return TemplateCustom|TemplateFile
     */
    public function getTemplate($name)
    {
        $custom = $this->em->getRepository(TemplateEntity::class)->getTemplateByName($name);
        if ($custom) {
            $template = TemplateCustom::createFromEntity($custom);
        } else {
            $template = new TemplateFile($name);
            if (!file_exists($template->getFilePath())) {
                throw new InvalidArgumentException('File does not exists');
            }
        }

        return $template;
    }

    /**
     * Returns a custom template.
     *
     * This is the same as getTemplate except we create a new TemplateCustom wrapper around
     * a TemplateFile if the template isn't custom (e.g., we are saving a customised version of a file
     * template for the fist time.)
     *
     * @param string $name
     *
     * @return TemplateCustom
     */
    public function getCustomTemplate($name)
    {
        $template = $this->getTemplate($name);
        if ($template instanceof TemplateCustom) {
            return $template;
        }

        $entity       = new TemplateEntity();
        $entity->name = $name;
        $custom       = null;

        if ($entity->name) {
            $custom = TemplateCustom::createFromEntity($entity);
            $custom->getTemplateCode()->setCode($template->getTemplateCode()->getCode());

            $entity->setTemplate(
                $custom->getTemplateCode()->getCode(),
                $this->compileTemplate($custom)
            );
        }

        return $custom;
    }

    /**
     * @param string $name
     * @param null   $customType
     *
     * @return TemplateCustom
     */
    public function createCustomTemplate($name, $customType = null)
    {
        $entity       = new TemplateEntity();
        $entity->name = $name;

        $custom = TemplateCustom::createFromEntity($entity, $customType);

        return $custom;
    }

    /**
     * Persists code saved in the template_code.
     *
     * @param TemplateCustom $template
     */
    public function saveTemplate(TemplateCustom $template)
    {
        $entity       = $template->getEntity();
        $entity->name = $template->getName();
        $entity->setTemplate(
            $template->getTemplateCode()->getCode(),
            $this->compileTemplate($template)
        );
        $entity->date_updated = new \DateTime();

        $this->em->persist($entity);
        $this->em->flush();
    }

    /**
     * Delete a template.
     *
     * @param TemplateCustom $template
     */
    public function deleteTemplate(TemplateCustom $template)
    {
        $entity = $template->getEntity();

        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
     * @param Template $template
     *
     * @return string
     */
    public function compileTemplate(Template $template)
    {
        $name = $template->getName();
        if ($template->isCustom() && $template->getOriginalName() != $name) {
            $name = $template->getOriginalName();
        }

        $templateCode = $template->getTemplateCode();
        if ($templateCode instanceof EmailTemplateCode) {
            $proc        = new EmailPreProcessor();
            $compileCode = $proc->process($templateCode->getCode(), $template->getName());
        } else {
            $compileCode = $templateCode->getCode();
        }

        if ($template->isCustom()) {
            $compileCode = $this->preProcessCustomTemplate($compileCode);
        }

        $compiled = $this->twig->compileSource($compileCode, $name);

        return $compiled;
    }

    /**
     * @param $code
     *
     * @return mixed
     */
    private function preProcessCustomTemplate($code)
    {
        $code = preg_replace('#\{%\s*include\s+(\'|")(.*?)(\'|")\s+#', '{% include \'$2\' ignore missing ', $code);

        return $code;
    }

    /**
     * @param Template  $template
     * @param Translate $tr
     * @param bool      $replacePhrases
     *
     * @return array
     */
    public function exportTemplateToArray(Template $template, Translate $tr = null, $replacePhrases = false)
    {
        $data                          = [];
        $data['name']                  = $template->getName();
        $data['base_name']             = $data['name'];
        $data['type']                  = $template->getType();
        $data['is_custom']             = $template->isCustom();
        $data['template_code']         = [];
        $data['template_code']['code'] = $template->getTemplateCode()->getCode();

        if ($template->getType() == 'email') {
            $data['template_code']['subject'] = $template->getTemplateCode()->getSubject();
            $data['template_code']['body']    = $template->getTemplateCode()->getBody();
        }

        if ($template->getOriginalName()) {
            $data['base_name'] = $template->getOriginalName();
            $data['original']  = [
                'name'          => $template->getOriginalName(),
                'template_code' => [],
            ];

            $data['original']['template_code']['code'] = $template->getTemplateCode()->getCode();
            if ($template->getType() == 'email') {
                $data['original']['template_code']['subject'] = $template->getTemplateCode()->getSubject();
                $data['original']['template_code']['body']    = $template->getTemplateCode()->getBody();
            }

            $data['original']['exists'] = $template->getOriginalContent() !== null;
        }

        if ($tr) {
            if (preg_match('#^DeskPRO:email#', $data['name']) && !preg_match('#^DeskPRO:emails_custom#', $data['name'])) {
                $templatesDesc               = new EmailTemplatesDesc();
                $info                        = $templatesDesc->getTplDisplayInfo(['name' => $data['name']], $tr);
                $data['display_title']       = $info['title'];
                $data['display_description'] = $info['desc'];
            } else {
                $name                        = Strings::extractRegexMatch('#^DeskPRO:.*?:(.*?).html.twig$#', $data['name'], 1).'.html';
                $key                         = 'admin.emailtpl_desc.'.strtolower(str_replace([':', '.'], '_', $data['base_name']));
                $data['display_title']       = $tr->hasPhrase($key.'_title') ? $tr->phrase($key.'_title') : $name;
                $data['display_description'] = $tr->hasPhrase($key.'_desc') ? $tr->phrase($key.'_desc') : null;
            }
        }

        if ($tr && $replacePhrases) {
            foreach ($data['template_code'] as $k => $v) {
                $data['template_code'][$k] = $this->resolvePhraseTags($v, $tr);
            }
        }

        return $data;
    }

    /**
     * Replace phrase tags with actual language.
     *
     * @param string    $code
     * @param Translate $tr
     *
     * @return string
     */
    public function resolvePhraseTags($code, Translate $tr)
    {
        $code = preg_replace_callback('#\{\{\s*phrase\((\"|\')([a-zA-Z0-9_\-\.]+)\\1\)\s*\}\}#', function ($m) use ($tr) {
            $phrase = $tr->getPhraseText($m[2], null, true);
            if ($phrase && strpos('|', $phrase) === false) {
                return $phrase;
            } else {
                return $m[0];
            }
        }, $code);

        return $code;
    }
}
