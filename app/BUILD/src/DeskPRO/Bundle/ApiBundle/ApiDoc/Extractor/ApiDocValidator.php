<?php

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor;

/**
 * Validate api doc annotations.
 * We need to make sure all actions have description, input/output info etc.
 *
 * Class ApiDocValidator.
 */
class ApiDocValidator
{
    /**
     * @param array $extracted
     * @param bool  $strict
     *
     * @return array
     */
    public function validate(array $extracted, $strict = false)
    {
        $failures = [];

        foreach ($extracted as $action) {
            /** @var \DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc $annotation */
            $annotation = $action['annotation'];
            $path       = $annotation->getRoute()->getPath();
            $method     = $annotation->getMethod();

            $missing = [];
            if (!$annotation->getSection()) {
                $missing[] = 'section';
            }
            if (!$annotation->getDescription()) {
                $missing[] = 'description';
            }
            if (in_array($method, ['GET', 'POST'])
                && !$annotation->getOutput()
                && !$annotation->isNoOutput()) {
                $missing[] = 'output';
            }
            if (in_array($method, ['POST', 'PUT'])
                && !$annotation->getInput()
                && !$annotation->getParameters()
                && !$annotation->isNoInput()) {
                $missing[] = 'input';
            }
            if ($strict && !$annotation->getDocumentation() && !$annotation->getDocumentationOverride()) {
                $missing[] = 'documentation';
            }

            if (count($missing)) {
                $failures[] = [
                    'method'  => $method,
                    'path'    => $path,
                    'missing' => $missing,
                ];
            }
        }

        return $failures;
    }
}
