Feature: Usersource Settings

  Background:
    Given I'm authenticated as "admin"

  Scenario: I retrieve usersource settings
    When I send a GET request to "/api/v2/settings/user_source"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.has_agent_login_form" should be equal to 1
    And the JSON node "data.has_user_login_form" should be equal to 1
    And the JSON node "data.reg_enabled" should be equal to 1
