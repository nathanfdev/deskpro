Feature: Portal Api Authorization
  Get an auth code for crossdomain requests

  Background: Fresh database
    Given I install the fresh data set

  Scenario: I get a new session code
    Given the setting "core.site_name" is set to "My helpdesk"
    When I send a POST request to "/portal/api/auth/session"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.session_code" should exist
    And the JSON node "data.person" should exist
    And the JSON node "data.global_settings.company.name" should be equal to "My helpdesk"

  Scenario: I re use stored session code
    Given I have guest portal api session with code "BKNPKHB2A9N9SA8"
    And I send a POST request to "/portal/api/auth/session" with parameters:
      | key   | value             |
      | dpsid | 2-BKNPKHB2A9N9SA8 |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.session_code" should be equal to "2-BKNPKHB2A9N9SA8"
    And the JSON node "data.person" should be null

  Scenario: I try to re use wrong session code
    And I send a POST request to "/portal/api/auth/session" with parameters:
      | key   | value             |
      | dpsid | 2-BKNPKHB2A9N9SA9 |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.session_code" should not contain "BKNPKHB2A9N9SA9"
    And the JSON node "data.person" should be null

  Scenario Outline: I change chat settings
    Given the setting "portal.chat.email_validation" is set to <email_validation>
    Given the setting "portal.chat.require_login" is set to <require_login>
    When I send a POST request to "/portal/api/auth/session"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.global_settings.chat.email_validation" should be equal to "<email_validation>"
    And the JSON node "data.global_settings.chat.require_login" should be equal to "<require_login>"

    Examples:
      | email_validation | require_login |
      | 0                | 0             |
      | 0                | 1             |
      | 1                | 0             |
      | 1                | 1             |
