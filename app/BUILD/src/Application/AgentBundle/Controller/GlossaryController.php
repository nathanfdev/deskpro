<?php

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\GlossaryWord;
use Application\DeskPRO\Entity\GlossaryWordDefinition;

/**
 * Glossary listing and editing.
 */
class GlossaryController extends AbstractController
{
    public function glossaryNewWordJsonAction()
    {
        $words = $this->in->getCleanValueArray('words', 'string');

        $brandId = $this->in->getUInt('brand_id');
        /** @var Brand $brand */
        $brand = $this->em->getRepository(Brand::class)->find($brandId);

        $definition = new GlossaryWordDefinition();
        $definition->setDefinition($this->in->getString('definition'));
        foreach ($words as $word) {
            $definition->addNewWord($word, $brand);
        }

        if (!count($definition->getWords())) {
            return $this->createJsonResponse(['error' => 'no_word']);
        }

        $this->em->persist($definition);
        $this->em->flush();

        return $this->createJsonResponse([
            'definition_id' => $definition['id'],
            'words'         => $words,
            'definition'    => $definition['definition'],
        ]);
    }

    public function glossarySaveWordJsonAction($word_id)
    {
        /** @var GlossaryWord $word */
        $word = $this->em->find(GlossaryWord::class, $word_id);
        if (!$word || !$word->getDefinition()) {
            return $this->createJsonResponse(['error' => 'not_found']);
        }

        $words = $this->in->getCleanValueArray('words', 'string');
        if (!$words) {
            return $this->createJsonResponse(['error' => 'no_word']);
        }

        $brandId = $this->in->getUInt('brand_id');
        /** @var Brand $brand */
        $brand = $this->em->getRepository(Brand::class)->find($brandId);

        $definition = $word->getDefinition();

        $definition['definition'] = $this->in->getString('definition');
        $definition->updateWords($words, $brand);

        if (!count($definition->getWords())) {
            return $this->createJsonResponse(['error' => 'no_word']);
        }

        $this->em->persist($definition);
        $this->em->flush();

        return $this->createJsonResponse([
            'definition_id' => $definition['id'],
            'words'         => $words,
            'definition'    => $definition['definition'],
        ]);
    }

    public function glossaryDeleteWordJsonAction($word_id)
    {
        $word = $this->em->find(GlossaryWord::class, $word_id);
        if (!$word || !$word->definition) {
            return $this->createJsonResponse(['error' => 'not_found']);
        }

        $definition = $word->definition;

        $words = [];
        foreach ($definition->words as $word) {
            $words[] = $word->word;
        }

        $this->em->remove($definition);
        $this->em->flush();

        return $this->createJsonResponse([
            'definition_id' => $definition['id'],
            'words'         => $words,
            'definition'    => $definition['definition'],
        ]);
    }

    public function glossaryWordJsonAction($word_id)
    {
        $word = $this->em->find(GlossaryWord::class, $word_id);
        if (!$word || !$word->definition) {
            return $this->createJsonResponse(['error' => 'not_found']);
        }

        $definition = $word->definition;

        $words = [];
        foreach ($definition->words as $def_word) {
            $words[] = $def_word->word;
        }

        return $this->createJsonResponse([
            'id'            => $word['id'],
            'definition_id' => $definition['id'],
            'words'         => $words,
            'definition'    => $definition['definition'],
        ]);
    }

    public function tipAction($word)
    {
        try {
            $word = $this->em->getRepository(GlossaryWord::class)->findOneByWord($word);
            $def  = $word->definition->definition;
        } catch (\Exception $e) {
            $def = '';
        }

        return $this->createResponse($def);
    }
}
