Feature: /mass_actions/tickets endpoint
  To complete mass actions on tickets list
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @basic
  Scenario: I apply set of actions on task with ID=1
    When I send a POST request to "/api/v2/mass_actions/tasks" with body:
"""
{
  "ids": [1],
  "params":{
     "set_due_date": "2016-04-15",
     "set_project": 1,
     "set_status": 1,
     "assign": {
        "agent": 1
     }
  }
}
    """
    Then the response status code should be 200

  Scenario: I get task with ID=1 and check if all mass actions was applied
    When I send a GET request to "/api/v2/tasks/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.project" should be equal to 1
    And the JSON node "data.date_due" should be equal to "2016-04-15T00:00:00+0000"
    And the JSON node "data.agents[0]" should be equal to 1

  Scenario: I want delete task with ID=1
    When I send a POST request to "/api/v2/mass_actions/tasks" with body:
"""
{
  "ids": [1],
  "params":{
     "set_of_actions": ["delete"]
  }
}
    """
    Then the response status code should be 200
