<?php

namespace DeskPRO\Bundle\AppBundle\Helper;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Phrase;
use Application\DeskPRO\Languages\LangPackInfo;
use Application\DeskPRO\Languages\PhraseData;
use Doctrine\ORM\EntityManager;
use Orb\Util\Numbers;

/**
 * Class LangPhraseHelper
 */
class LangPhraseHelper
{
    public const LOCALES_LOCATION = DP_ROOT.'/locales';

    /**
     * @var DeskproContainer
     */
    private $container;
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * LangPhraseHelper constructor.
     *
     * @param DeskproContainer $container
     * @param EntityManager $em
     */
    public function __construct(DeskproContainer $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em        = $em;
    }

    /**
     * @param $langId
     * @param $group
     *
     * @return array
     */
    public function getPhraseIds($langId, $group)
    {
        $phrases = $this->getLanguagePhrases($langId, $group);

        if (empty($phrases)) {
            return [];
        }

        return array_map(static function ($arr) {
            return $arr['id'];
        }, $phrases);
    }

    /**
     * @param $phraseId
     * @param $langId
     * @param $group
     *
     * @return bool
     */
    public function isLanguagePhraseExists($phraseId, $langId, $group)
    {
        $phrasesIds = $this->getPhraseIds($langId, $group);

        return in_array($phraseId, $phrasesIds, true);
    }

    /**
     * @param $langId
     * @param $group
     *
     * @return array|false
     */
    public function getLanguagePhrases($langId, $group)
    {
        $lang = $this->getLang($langId);

        if (!$lang) {
            return false;
        }

        /** @var \Application\DeskPRO\EntityRepository\Phrase $phraseRepo */
        $phraseRepo = $this->em->getRepository(Phrase::class);
        $phraseData = new PhraseData($phraseRepo, self::LOCALES_LOCATION);

        switch ($group) {
            case 'ticket_departments':
                $phrases = $phraseData->getTicketDepartmentPhrases(
                    $this->container->getSystemService('ticket_departments'),
                    $lang
                );

                break;

            case 'ticket_categories':
                $phrases = $phraseData->getTicketCategoryPhrases(
                    $this->container->getSystemService('ticket_categories'),
                    $lang
                );

                break;

            case 'ticket_priorities':
                $phrases = $phraseData->getTicketPriorityPhrases(
                    $this->container->getSystemService('ticket_priorities'),
                    $lang
                );

                break;

            case 'chat_departments':
                $phrases = $phraseData->getChatDepartmentPhrases(
                    $this->container->getSystemService('chat_departments'),
                    $lang
                );

                break;

            case 'products':
                $phrases = $phraseData->getProductPhrases(
                    $this->container->getSystemService('products'),
                    $lang
                );

                break;

            case 'ticket_fields':
                $phrases = $phraseData->getFieldPhrases(
                    $this->container->getSystemService('ticket_fields_manager'),
                    $lang
                );

                break;

            case 'person_fields':
                $phrases = $phraseData->getFieldPhrases(
                    $this->container->getSystemService('person_fields_manager'),
                    $lang
                );

                break;

            case 'org_fields':
                $phrases = $phraseData->getFieldPhrases(
                    $this->container->getSystemService('org_fields_manager'),
                    $lang
                );

                break;

            case 'chat_fields':
                $phrases = $phraseData->getFieldPhrases(
                    $this->container->getSystemService('chat_fields_manager'),
                    $lang
                );

                break;

            case 'community_statuses':
                $phrases = $phraseData->getCommunityStatusPhrases($lang);

                break;

            case 'community_forums':
                $phrases = $phraseData->getCommunityForumsPhrases($lang);

                break;

            case 'kb_categories':
                /** @var \Application\DeskPRO\EntityRepository\ArticleCategory $repos */
                $repos   = $this->em->getRepository(ArticleCategory::class);
                $phrases = $phraseData->getKbCategoryPhrases($repos, $lang);

                break;

            case 'custom':
                $phrases = $phraseData->loadCustom($lang);

                break;

            default:
                $phrases = $phraseData->loadGroup($lang, $group);

                break;
        }

        return $phrases;
    }

    /**
     * @param $id
     *
     * @return Language|false
     */
    public function getLang($id)
    {
        if (Numbers::isInteger($id)) {
            $lang = $this->container->getLanguageData()->get($id);
            if (!$lang) {
                return false;
            }
        } else {
            $langpacks = new LangPackInfo();
            if (!$langpacks->hasLang($id)) {
                return false;
            }

            $lang_info = $langpacks->getLangInfo($id);
            $lang      = false;

            foreach ($this->container->getLanguageData()->getAll() as $l) {
                if ($l->sys_name === $lang_info['id']) {
                    $lang = $l;

                    break;
                }
            }

            if (!$lang) {
                $lang = false;
            }
        }

        return $lang;
    }
}
