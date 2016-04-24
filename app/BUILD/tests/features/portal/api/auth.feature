Feature: Portal Api Authorization
  Get an auth code for crossdomain requests

  Background: Fresh database
    Given I install the fresh data set

  Scenario: I get a new session code
    When I send a POST request to "/portal/api/auth/get_session"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.session_code" should exist
    And the JSON node "data.person" should exist

  Scenario: I re use stored session code
    Given I have guest portal api session with code "BKNPKHB2A9N9SA8"
    And I send a POST request to "/portal/api/auth/get_session" with parameters:
      | key          | value             |
      | session_code | 2-BKNPKHB2A9N9SA8 |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.session_code" should be equal to "2-BKNPKHB2A9N9SA8"
    And the JSON node "data.person" should be null

  Scenario: I try to re use wrong session code
    And I send a POST request to "/portal/api/auth/get_session" with parameters:
      | key          | value             |
      | session_code | 2-BKNPKHB2A9N9SA9 |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.session_code" should not contain "BKNPKHB2A9N9SA9"
    And the JSON node "data.person" should be null
