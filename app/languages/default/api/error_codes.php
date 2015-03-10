<?php return array(

    # General (ApiErrors)
    'api.error_codes.error' => 'Error.',
    'api.error_codes.invalid' => 'Invalid.',
    'api.error_codes.internal_error' => 'Internal error.',
    'api.error_codes.bad_request' => 'Request is invalid.',
    'api.error_codes.invalid_input' => 'Request input is invalid.',
    'api.error_codes.unauthorized' => 'You are not authorized to make this request.',
    'api.error_codes.not_authenticated' => 'You must be authenticated to make this request.',
    'api.error_codes.not_found' => 'The requested resource was not found.',
    'api.error_codes.invalid_jsonp_callback' => 'The JSONP callback parameter is invalid. Please use a JSONP callback is is not a Javascript reserved word.',
    'api.error_codes.invalid_json_body' => 'The request JSON body is not valid JSON.',
    'api.error_codes.invalid_data_type' => 'This data type is not is data type that was expected.',

    # Length
    'api.error_codes.length_too_long' => 'This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.',
    'api.error_codes.length_too_short' => 'This value is too short. It should have {{ limit }} character or more.|This value is too short. It should have {{ limit }} characters or more.',
    'api.error_codes.length_invalid' => 'This value should have exactly {{ limit }} character.|This value should have exactly {{ limit }} characters.',
    'api.error_codes.invalid_charset' => 'This value does not match the expected {{ charset }} charset.',

    # NotNull/NotBlank
    'api.error_codes.required' => 'This value should not be blank.',

    # Type
    'api.error_codes.wrong_type' => 'This value should be of type {{ type }}.',
);