<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

return [
    'api.error_codes.already_in_organization'          => 'That user is already in an organization.',
    'api.error_codes.bad_choice'                       => 'One or more of the given values is invalid.',
    'api.error_codes.bad_credentials'                  => 'Bad credentials.',
    'api.error_codes.bad_request'                      => 'Request is invalid.',
    'api.error_codes.banned_email'                     => 'Email "{{ email }}" is banned.',
    'api.error_codes.dupe_email'                       => 'Email "{{ email }}" is already in use by other user.',
    'api.error_codes.dupe_ticket'                      => 'Duplicate ticket.',
    'api.error_codes.dupe_ticket_message'              => 'Duplicate ticket message.',
    'api.error_codes.error_ticket_opened'              => 'Ticket should be neither resolved nor archived.',
    'api.error_codes.email_already_validated'          => 'Email is already validated.',
    'api.error_codes.email_wrong_validation_code'      => 'Wrong email validation code.',
    'api.error_codes.empty'                            => 'This value should be blank.',
    'api.error_codes.error'                            => 'Error.',
    'api.error_codes.extra_fields'                     => 'Unexpected field names: {{ extra_fields }}',
    'api.error_codes.facebook_url'                     => 'This value is not a valid profile URL.',
    'api.error_codes.forbidden'                        => 'You are not authorized to make this request.',
    'api.error_codes.internal_error'                   => 'Internal error.',
    'api.error_codes.invalid'                          => 'Invalid.',
    'api.error_codes.invalid_api_key'                  => 'Invalid API key.',
    'api.error_codes.invalid_api_token'                => 'Invalid API token.',
    'api.error_codes.invalid_authorization_header'     => 'Invalid Authorization header (type can be one of "key" or "token").',
    'api.error_codes.invalid_charset'                  => 'This value does not match the expected {{ charset }} charset.',
    'api.error_codes.invalid_data_type'                => 'This data type is not is data type that was expected.',
    'api.error_codes.invalid_email'                    => 'This value "{{ value }}" is not a valid email address.',
    'api.error_codes.invalid_input'                    => 'Request input is invalid.',
    'api.error_codes.invalid_json_body'                => 'The request JSON body is not valid JSON.',
    'api.error_codes.invalid_jsonp_callback'           => 'The JSONP callback parameter is invalid. Please use a JSONP callback is is not a Javascript reserved word.',
    'api.error_codes.invalid_phone_number_format'      => 'Invalid phone number format.',
    'api.error_codes.invalid_session_id'               => 'Invalid session ID.',
    'api.error_codes.invalid_url'                      => 'This value is not a valid URL.',
    'api.error_codes.length_invalid'                   => 'This value should have exactly {{ limit }} characters.',
    'api.error_codes.length_too_long'                  => 'This value is too long. It should have {{ limit }} characters or less.',
    'api.error_codes.length_too_short'                 => 'This value is too short. It should have {{ limit }} characters or more.',
    'api.error_codes.link_itself'                      => 'The object should not link itself.',
    'api.error_codes.linked_in_url'                    => 'This value is not a valid profile URL.',
    'api.error_codes.malformed_authorization_header'   => 'Malformed Authorization header (should be "Authorization: type value").',
    'api.error_codes.no_uploaded_file'                 => 'Uploaded file was not found.',
    'api.error_codes.not_assignable_ticket_department' => 'Unable to select parent department.',
    'api.error_codes.not_checked'                      => 'The field is not checked.',
    'api.error_codes.not_found'                        => 'The requested resource was not found.',
    'api.error_codes.not_unique_collection'            => 'One or more of the given values is not unique.',
    'api.error_codes.numeric'                          => 'Please enter a number, with no other characters.',
    'api.error_codes.only_one_value'                   => 'You should set exactly only one of {{ values }}',
    'api.error_codes.op_not_supported'                 => 'Op "{{ op }}" not supported. Supported ops are: {{ ops }}',
    'api.error_codes.person_not_agent'                 => 'Person with identifier "{{ value }}" is not an agent.',
    'api.error_codes.person_not_found'                 => 'Person with identifier "{{ value }}" not found.',
    'api.error_codes.person_not_user'                  => 'Person with identifier "{{ value }}" is not a user.',
    'api.error_codes.required'                         => 'This value should not be blank.',
    'api.error_codes.resource_not_found'               => 'The value was not found.',
    'api.error_codes.system_email'                     => 'Email "{{ email }}" is already being used as email account.',
    'api.error_codes.term_type_does_not_exist'         => 'You tried to create a term with the type code "{{ type }}", but it does not exist. Please check your term types.',
    'api.error_codes.too_few_elements'                 => 'This collection should contain {{ limit }} elements or more.',
    'api.error_codes.too_low'                          => 'This value should be greater than or equal to {{ compared_value }}.',
    'api.error_codes.too_many_elements'                => 'This collection should contain {{ limit }} elements or less.',
    'api.error_codes.unauthorized'                     => 'You must be authenticated to make this request.',
    'api.error_codes.unauthorized_check_server'        => 'Unauthorized. If you are using apache and feel that this is incorrect, please see this article for help: https://support.deskpro.com/kb/articles/415',
    'api.error_codes.unique_entity'                    => 'This value already exists in the system.',
    'api.error_codes.not_an_image'                     => 'Uploaded file is not an image',
];
