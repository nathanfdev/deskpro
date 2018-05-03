<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

class Constants
{
    /** path to the manifest file inside a bundle package */
    const BUNDLE_MANIFEST_PATH = 'manifest.json';

    const PERMISSION_OWNER = 'OWNER';

    const PERMISSION_EVERYONE = 'EVERYBODY';

    const ACCESS_LEVEL_READ = 'READ';

    const ACCESS_LEVEL_WRITE = 'WRITE';

    const ACCESS_SERVICE_API = 'API';

    const ACCESS_SERVICE_PROXY = 'PROXY';

    const APPLICATION_SCOPE_AGENT = 'agent';

    const APPLICATION_SCOPE_USER = 'user';

    /** @deprecated */
    const STATE_TARGET_APPLICATION = 'app';

    /** @deprecated */
    const STATE_PERMISSION_SHARED = 'shared';

    /** @deprecated */
    const STATE_PERMISSION_PRIVATE = 'private';
}
