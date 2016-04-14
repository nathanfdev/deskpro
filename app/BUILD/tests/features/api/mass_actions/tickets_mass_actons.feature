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

  Scenario: I apply set of actions on ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{"set_status": "awaiting_agent"}
}
    """
    And print last JSON response
    Then the response status code should be 200

  Scenario: I get ticket with ID=1 and it's status should be equal 'awaiting_agent'
    When I send a GET request to "/api/v2/tickets/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.status" should be equal to "awaiting_agent"


  Scenario: I apply set of actions on ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{
     "set_category": 1,
     "set_product": 1,
     "set_language": 1,
     "set_workflow": 1,
     "assign": {
        "agent": 1,
        "team": 1,
        "department": 1
     },
     "set_of_actions": ["mark_as_spam"]
  }
}
    """
    And print last JSON response
    Then the response status code should be 200

  Scenario: I set incorrect status = 1 for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
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
    And the JSON node "message" should be equal to 'The option "options" with value "1" is invalid. Accepted values are: "awaiting_agent", "awaiting_user", "resolved", "archived".'

  Scenario: I try to set non-existing product for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{"set_product":2000}
}
    """
    Then the response status code should be 400
    And print last JSON response
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "Product with ID=2000 doesn't exists"

  Scenario: I try to set non-existing agent for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{"assign":{"agent":2000}}
}
    """
    And print last JSON response
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "Agent with ID=2000 doesn't exists"

  Scenario: I try to set non-existing agents team for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{"assign":{"team":2000}}
}
    """
    And print last JSON response
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "Agents team with ID=2000 doesn't exists"

  Scenario: I try to set non-existing department for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{"assign":{"department":2000}}
}
    """
    And print last JSON response
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "Department with ID=2000 doesn't exists"

  Scenario: I try to set non-existing category for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{"set_category":2000}
}
    """
    And print last JSON response
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "Category with ID=2000 doesn't exists"

  Scenario: I try to set non-existing language for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{"set_language":2000}
}
    """
    And print last JSON response
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "Language with ID=2000 doesn't exists"

  Scenario: I try to set non-existing workflow for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{"set_workflow":2000}
}
    """
    And print last JSON response
    Then the response status code should be 400
    And the JSON node "status" should exist
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should exist
    And the JSON node "message" should be equal to "Workflow with ID=2000 doesn't exists"

  Scenario: I get ticket with ID=1 and check if all mass actions was applied
    When I send a GET request to "/api/v2/tickets/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "spam"
    And the JSON node "data.product" should be equal to 1
    And the JSON node "data.agent_team" should be equal to 1
    And the JSON node "data.department" should be equal to 1
    And the JSON node "data.category" should be equal to 1
    And the JSON node "data.language" should be equal to 1
