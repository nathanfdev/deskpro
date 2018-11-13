<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security;

class OauthErrorCodes extends \Exception
{
    const CODE_BAD_CREDENTIALS = 1;

    const CODE_PROVIDER_NOT_FOUND = 2;

    const CODE_CONNECTION_NOT_FOUND = 3;

    const CODE_BAD_REQUEST = 4;

    const CODE_GENERIC_FAILURE = 5;
}
