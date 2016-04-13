Feature: /mass_actions endpoint
  To complete mass actions on item's lists
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

  Scenario: I post mass actions request without ids
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "actions":{"set_status":"awaiting_agent"}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "You must select tickets for mass action apply"

  Scenario: I post mass actions request with empty ids
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [],
  "actions":{"set_status":"awaiting_agent"}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "You must select tickets for mass action apply"

  Scenario: I post mass actions request without ids
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "actions":{"set_status":"awaiting_agent"}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "You must select tickets for mass action apply"

  Scenario: I post mass actions request with empty actions
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": ["1"],
  "actions":{}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "You must define one or more actions"

  Scenario: I post mass actions request without actions
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": ["1"]
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "You must define one or more actions"

  Scenario: I set incorrect status = 1 fot ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": ["1"],
  "actions":{"set_status":1}
}
    """
    Then the response status code should be 500
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 500
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'The option "options" with value "1" is invalid. Accepted values are: "awaiting_agent", "awaiting_user", "resolved", "archived".'

  Scenario: I send POST request for non-existent type of content
    When I send a POST request to "/api/v2/mass_actions/something" with body:
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
    And the JSON node "message" should be equal to 'You try to apply mass actions for non-existent type of content (`something`)'
