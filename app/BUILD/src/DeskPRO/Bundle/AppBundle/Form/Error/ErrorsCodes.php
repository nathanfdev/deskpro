<?php

namespace DeskPRO\Bundle\AppBundle\Form\Error;

/**
 * Class ErrorsCodes.
 */
final class ErrorsCodes
{
    /** The absolute last fallback error code for thrown exceptions */
    const EXCEPTION_FALLBACK = 'error';

    /** The absolute last fallback error code for form/validator errors */
    const CONSTRAINT_FALLBACK = 'invalid';

    /** General purpose error codes */
    const INTERNAL_ERROR            = 'internal_error';
    const BAD_REQUEST               = 'bad_request';
    const RATE_LIMITS               = 'rate_limits';
    const INVALID_INPUT             = 'invalid_input';
    const FORBIDDEN                 = 'unauthorized';
    const UNAUTHORIZED              = 'unauthorized';
    const UNAUTHORIZED_CHECK_APACHE = 'unauthorized_check_server';
    const NOT_FOUND                 = 'not_found';
    const INVALID_JSONP_CALLBACK    = 'invalid_jsonp_callback';
    const INVALID_JSON_BODY         = 'invalid_json_body';
    const INVALID_DATA_TYPE         = 'invalid_data_type';
    const EXTRA_FIELDS              = 'extra_fields';
    const IP_VERIFY                 = 'ip_verify';

    /** Some specific authentication codes */
    const INVALID_SESSION_ID             = 'invalid_session_id';
    const INVALID_API_KEY                = 'invalid_api_key';
    const INVALID_API_TOKEN              = 'invalid_api_token';
    const INVALID_CORS_AUTH_TYPE         = 'invalid_cors_auth_type';
    const INVALID_AUTHORIZATION_HEADER   = 'invalid_authorization_header';
    const MALFORMED_AUTHORIZATION_HEADER = 'malformed_authorization_header';
    const BAD_CREDENTIALS                = 'bad_credentials';
    const CAPTCHA_REQUIRED               = 'captcha_required';
    const CSRF                           = 'csrf';

    /** Validator/Constraint specific error codes */
    const NULL                        = 'empty';
    const NOT_NULL                    = 'required';
    const NOT_BLANK                   = 'required';
    const LENGTH_TOO_LONG             = 'length_too_long';
    const LENGTH_TOO_SHORT            = 'length_too_short';
    const LENGTH_INVALID              = 'length_invalid';
    const BAD_CHOICE                  = 'bad_choice';
    const UNIQUE_ENTITY               = 'unique_entity';
    const INVALID_EMAIL               = 'invalid_email';
    const INVALID_URL                 = 'invalid_url';
    const INVALID_IP                  = 'invalid_ip';
    const TOO_FEW_ELEMENTS            = 'too_few_elements';
    const TOO_MANY_ELEMENTS           = 'too_many_elements';
    const TOO_LOW                     = 'too_low';
    const TOO_HIGH                    = 'too_high';
    const EMAIL_ALREADY_VALIDATED     = 'email_already_validated';
    const EMAIL_WRONG_VALIDATION_CODE = 'email_wrong_validation_code';
    const NO_PERSON                   = 'person_not_found';
    const NOT_AGENT                   = 'person_not_agent';
    const NOT_USER                    = 'person_not_user';
    const NUMERIC                     = 'numeric';
    const NOT_CHECKED                 = 'not_checked';
    const REGEX                       = 'regex';
    const NO_UPLOADED_FILE            = 'no_uploaded_file';
    const NOT_AN_IMAGE                = 'not_an_image';
    const MISMATCH_VALUES             = 'mismatch_values';

    /** Term Engine Specific */
    const TERM_TYPE_DOES_NOT_EXIST = 'term_type_does_not_exist';
}
