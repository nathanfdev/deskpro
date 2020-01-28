<?php

namespace DeskPRO\Bundle\AppBundle\Security\Http;

/**
 * Class HttpUtils.
 */
class HttpUtils extends \Symfony\Component\Security\Http\HttpUtils
{
    /**
     * {@inheritdoc}
     */
    public function generateUri($request, $path)
    {
        // expose non-http schemes
        // to support internal device urls like 'android-app://com.google.android.gm'
        if (preg_match('#^\w+://.+#', $path)) {
            return $path;
        }

        try {
            $baseUrl = $request->getBaseUrl();
            // hacky but should work - strip off baseUrl from path before passing it to the parent
            if ($baseUrl && strpos($path, '/'.ltrim($baseUrl, '/')) === 0) {
                $path = substr($path, strlen('/'.ltrim($baseUrl, '/')));
            }

            return parent::generateUri($request, $path);
        } catch (\Exception $e) {
            return '/';
        }
    }
}
