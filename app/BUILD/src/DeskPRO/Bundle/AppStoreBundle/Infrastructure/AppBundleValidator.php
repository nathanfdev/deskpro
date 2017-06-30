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

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppStoreBundle\Domain;

class AppBundleValidator implements Domain\AppBundleValidator
{
    /** @var  \SplFileInfo */
    private $schema;

    /**
     * ApplicationService constructor.
     * @param \SplFileInfo $schema
     */
    public function __construct(\SplFileInfo $schema)
    {
        $this->schema = $schema;
    }

    public function validateBundle(Domain\AppBundle $bundle)
    {
        $manifestString = $bundle->getManifestAsString();
        $manifestData = json_decode($manifestString);
        if (empty($manifestData) || false == $manifestData instanceof \stdClass) {
            return false;
        }

        try {
            $schema = $this->decodeSchema($this->schema);
        } catch (\Exception $e) { // should log perhaps
            return false;
        }

        $dereferencer  = \League\JsonReference\Dereferencer::draft4();
        $schema = $dereferencer->dereference($schema);

        $validator     = new \League\JsonGuard\Validator($manifestData, $schema);
        return $validator->passes();
    }

    /**
     * @param \SplFileInfo $schema
     * @return \stdClass
     */
    private function decodeSchema(\SplFileInfo $schema)
    {
        $filePath = $schema->getRealPath();
        $contents = file_get_contents($filePath);
        if (false === $contents) {
            throw new \RuntimeException('could not read contents of app manifest schema file from: ' .$filePath);
        }

        $contents = json_decode($contents);
        if ($contents instanceof \stdClass) {
            return $contents;
        }

        throw new \RuntimeException('could not decode contents of app manifest schema file from: ' .$filePath);
    }

}


