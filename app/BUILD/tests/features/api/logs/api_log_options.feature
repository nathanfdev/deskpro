@new
Feature: /api_logs_options

  Background:
    Given I'm authenticated as "admin"
    And the setting "api_log.enabled" is set to 0
    And the setting "api_log.max_request_body_length" is set to 1000
    And the setting "api_log.max_response_body_length" is set to 2000
    And the setting "api_log.modes" is set to 'a:1:{i:0;s:5:"token";}'

  Scenario: I get options
    When I send a GET request to "/api/v2/api_logs_options"
    Then the JSON node "data.enabled" should be equal to 0
    And the JSON node "data.request_length" should be equal to 1000
    And the JSON node "data.response_length" should be equal to 2000
    And the JSON node "data.modes" should have 1 element
    And the JSON node "data.modes[0]" should be equal to "token"

  Scenario: I update option
    When I send a PUT request to "/api/v2/api_logs_options" with body:
    """
{
  "modes": ["key", "token"]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/api_logs_options"
    Then the JSON node "data.enabled" should be equal to 0
    And the JSON node "data.request_length" should be equal to 1000
    And the JSON node "data.response_length" should be equal to 2000
    And the JSON node "data.modes" should have 2 elements
    And the JSON node "data.modes[0]" should be equal to "token"
    And the JSON node "data.modes[1]" should be equal to "key"

  Scenario: I update all options
    When I send a PUT request to "/api/v2/api_logs_options" with body:
    """
{
  "enabled": true,
  "request_length": 3000,
  "response_length": 4000,
  "modes": ["session", "key", "token"]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/api_logs_options"
    Then the JSON node "data.enabled" should be equal to 1
    And the JSON node "data.request_length" should be equal to 3000
    And the JSON node "data.response_length" should be equal to 4000
    And the JSON node "data.modes" should have 3 elements
    And the JSON node "data.modes[0]" should be equal to "token"
    And the JSON node "data.modes[1]" should be equal to "session"
    And the JSON node "data.modes[2]" should be equal to "key"

  Scenario Outline: I validate request and response body length options
    When I send a PUT request to "/api/v2/api_logs_options" with body:
    """
{
  "<field>": "8bit"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.<field>.errors[0].code" should be equal to "invalid_data_type"

    Examples:
      | field           |
      | request_length  |
      | response_length |

  Scenario: I validate mode option
    When I send a PUT request to "/api/v2/api_logs_options" with body:
    """
{
  "modes": ["unknown mode"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.modes.errors[0].code" should be equal to "bad_choice"
