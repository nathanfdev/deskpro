Feature: /mass_actions endpoint
  To complete mass actions on item's lists
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I send POST request for non-existent type of content
    When I send a POST request to "/api/v2/mass_actions/something" with body:
    """
{
  "ids": [1],
  "params":{"set_status":1}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to 'You try to apply mass actions for non-existent type of content (`something`)'

  Scenario: I try to apply non-existed action on tickets
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{"non_existed":"anything"}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "Action class 'non_existed' doesn't exists"

  Scenario: I post mass actions request with empty ids
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [],
  "params":{"set_status":"awaiting_agent"}
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
  "params":{"set_status":"awaiting_agent"}
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
  "ids": [1],
  "params":{}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "You must define parameters for one or more actions"

  Scenario: I post mass actions request without actions
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1]
}
    """
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "You must define parameters for one or more actions"
