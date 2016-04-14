Feature: /mass_actions/tickets endpoint
  To complete mass actions on tickets list
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @basic
  Scenario: I get ticket with ID=1 and it's status should be equal 'awaiting_user'
    When I send a GET request to "/api/v2/tickets/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.status" should be equal to "awaiting_user"

  Scenario: I set status = awaiting_agent fot ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": ["1"],
  "actions":{"set_status":"awaiting_agent"}
}
    """
    Then the response status code should be 200

  Scenario: I get ticket with ID=1 and it's status should be equal 'awaiting_agent'
    When I send a GET request to "/api/v2/tickets/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.status" should be equal to "awaiting_agent"

  Scenario: I set incorrect status = 1 fot ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": ["1"],
  "actions":{"set_status":1}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "options" with value "1" is invalid. Accepted values are: "awaiting_agent", "awaiting_user", "resolved", "archived".'
