@new
Feature: /api_tokens endpoint

  Background:
    # this gives us the ability to assume, that users exists and they password is "password"
    Given "agent@deskpro.dev" agent exists

  Scenario: I fail to get a token because I make a bad request
    When I send a POST request to "/api/v2/api_tokens"
    And the response status code should be 400
    And the JSON node "status" should be equal to 400
    And the JSON node "code" should be equal to "invalid_input"
    And the JSON node "message" should be equal to "Request input is invalid."
    And the JSON node "errors.fields.email.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.email.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.password.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.password.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I fail to get a token because I use the wrong credentials
    When I send a POST request to "/api/v2/api_tokens" with body:
    """
{
  "email": "agent@deskpro.dev",
  "password": "wrong password"
}
    """
    Then the response status code should be 401
    And the header "WWW-Authenticate" should be equal to 'session,token,key realm="DeskPRO API"'
    And the JSON node "status" should be equal to 401
    And the JSON node "code" should be equal to "bad_credentials"
    And the JSON node "message" should be equal to "Bad credentials."

  Scenario: I successfully get a token
    Given "testagent@deskpro.dev" agent exists
    Given only the following "Usersource" records exist:
      | #   | title      | type  | source_type                                    | is_enabled |
      | us1 | Usersource | agent | Application\DeskPRO\Usersource\Adapter\DeskPRO | 1          |
    When I send a POST request to "/api/v2/api_tokens" with body:
    """
{
  "email": "testagent@deskpro.dev",
  "password": "password"
}
    """
    Then the response status code should be 201
    And the JSON node "data.token" should exist
    And the JSON node "data.person_id" should be equal to "{testagent@deskpro.dev}"
    And the JSON node "data.token" should exist

  Scenario: I authenticate a device
    Given a device setup auth code "code" for agent "agent"
    When I send a GET request to "/api/v2/api_tokens/device_setup/code"
    Then the response status code should be 201
    And the JSON node "data.token" should exist
    And the JSON node "data.person_id" should be equal to "{agent@deskpro.dev}"
    And the JSON node "data.token" should exist

  Scenario Outline: I get list of usersources
    When I send a GET request to "/api/v2/api_tokens/user_sources/<context>"
    Then the response status code should be 200

    Examples:
      | context |
      | user    |
      | agent   |

  Scenario: I handle user source callback with malformed request
    Given only the following "Usersource" records exist:
      | #   | title      | type | source_type                                              | is_enabled |
      | us1 | Usersource | user | DpTestSrc\TestBundle\Mock\Usersource\CallbackAdapterMock | 1          |
    When I send a GET request to "/api/v2/api_tokens/user_sources/{us1}/callback/default"
    Then the response status code should be 401
    And the JSON node "status" should be equal to 401
    And the JSON node "code" should be equal to "bad_credentials"
    And the JSON node "message" should be equal to "Bad credentials."

  Scenario: I successfully handle user source callback
    Given only the following "Usersource" records exist:
      | #   | title      | type | source_type                                              | is_enabled |
      | us1 | Usersource | user | DpTestSrc\TestBundle\Mock\Usersource\CallbackAdapterMock | 1          |
    And only the following "UsersourceAssoc" records exist:
      | #    | person  | usersource | identity | identity_friendly |
      | usa1 | {agent} | {us1}      | 1        | ''                |
    When I send a GET request to "/api/v2/api_tokens/user_sources/{us1}/callback/default"
    Then the response status code should be 200
    And the JSON response should contain "<script>sendPayload("
    And the JSON response should contain "person_id"
    And the JSON response should contain "token"

    When I send a GET request to "/api/v2/api_tokens/user_sources/{us1}/callback/ios"
    Then the response status code should be 200
    And the JSON response should contain "iOSDeskPro"
    And the JSON response should contain "Continue"
