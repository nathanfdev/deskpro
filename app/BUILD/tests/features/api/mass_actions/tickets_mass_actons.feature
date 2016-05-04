Feature: /mass_actions/tickets endpoint
  To complete mass actions on tickets list
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

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
  "ids": [1,2],
  "params":{"set_status": "awaiting_agent"}
}
    """
    Then the response status code should be 200

  Scenario: I get ticket with ID=1 and it's status should be equal 'awaiting_agent'
    When I send a GET request to "/api/v2/tickets/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.status" should be equal to "awaiting_agent"

  Scenario: I apply set of actions on tickets with ID=1,2
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1,2],
  "params":{
     "set_category": 1,
     "set_product": 1,
     "set_language": 1,
     "set_workflow": 1,
     "set_followers": [1, 2, 1000],
     "assign": {
        "agent": 1,
        "team": 1,
        "department": 2
     },
     "set_of_actions": ["mark_as_spam"]
  }
}
    """
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
    And the JSON node "message" should be equal to 'The option "set_status" with value "1" is invalid. Accepted values are: "awaiting_agent", "awaiting_user", "resolved", "archived".'

  Scenario: I try to set non-existing product for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{"set_product":2000}
}
    """
    Then the response status code should be 400
    And the JSON node "status" should be equal to 400
    And the JSON node "message" should be equal to "Product with ID=2000 doesn't exists"

  Scenario: I try to set non-existing agent for ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{"assign":{"agent":2000}}
}
    """
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
    And the JSON node "data.agent" should be equal to 1
    And the JSON node "data.agent_team" should be equal to 1
    And the JSON node "data.department" should be equal to 2
    And the JSON node "data.category" should be equal to 1
    And the JSON node "data.language" should be equal to 1
    And the JSON node "data.followers" should have 2 elements
    And the JSON node "data.followers[0]" should be equal to 1
    And the JSON node "data.followers[1]" should be equal to 2

  Scenario: I apply unassign followers on ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{
     "set_followers": []
  }
}
    """
    Then the response status code should be 200

  Scenario: I get ticket with ID=1 and check if all mass actions was applied
    When I send a GET request to "/api/v2/tickets/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "spam"
    And the JSON node "data.product" should be equal to 1
    And the JSON node "data.agent" should be equal to 1
    And the JSON node "data.agent_team" should be equal to 1
    And the JSON node "data.department" should be equal to 2
    And the JSON node "data.category" should be equal to 1
    And the JSON node "data.language" should be equal to 1
    And the JSON node "data.followers" should have 0 element

  Scenario: I apply unassign action on ticket with ID=1
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [1],
  "params":{
     "assign": {
        "agent":      null,
        "team":       null,
        "department": null
     }
  }
}
    """
    Then the response status code should be 200

  Scenario: I get ticket with ID=1 and check if unassign mass actions was applied
    When I send a GET request to "/api/v2/tickets/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.agent" should be null
    And the JSON node "data.agent_team" should be null
    And the JSON node "data.department" should be equal to 1

  Scenario: I delete ticket with ID=3,4 and check if delete mass actions was applied
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [3,4],
  "params":{
     "set_of_actions": ["delete"]
  }
}
    """
    Then the response status code should be 200

  Scenario: I get ticket with ID=3 and check if delete mass actions was applied
    When I send a GET request to "/api/v2/tickets/3"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "deleted"

  Scenario: I get ticket with ID=4 and check if delete mass actions was applied
    When I send a GET request to "/api/v2/tickets/4"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.status" should be equal to "hidden"
    And the JSON node "data.hidden_status" should be equal to "deleted"
