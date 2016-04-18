@tasks
Feature: /tasks endpoint
  To CRUD DeskPRO tasks
  As a developer
  I want to check creator filters

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I prepare tasks
    Given my request is authenticated to "admin"
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Admin task"
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    Given my request is authenticated to "agent"
    Given I set permission "agent_tasks.use" = 1 for "registered" usergroup
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Agent task"
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I don't filter tasks
    When I send a GET request to "/api/v2/tasks"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 4 elements

    And the JSON node "data[0].title" should be equal to "Agent task"
    And the JSON node "data[0].creator" should be equal to 2

    And the JSON node "data[1].title" should be equal to "Admin task"
    And the JSON node "data[1].creator" should be equal to 1

    And the JSON node "data[2].title" should be equal to "An unassigned task"
    And the JSON node "data[2].creator" should be equal to 1

    And the JSON node "data[3].title" should be equal to "A demo task"
    And the JSON node "data[3].creator" should be equal to 1

  Scenario: I filter agent tasks
    When I send a GET request to "/api/v2/tasks?creator=2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element

    And the JSON node "data[0].title" should be equal to "Agent task"
    And the JSON node "data[0].creator" should be equal to 2

  Scenario: I filter admin tasks
    When I send a GET request to "/api/v2/tasks?creator=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 3 elements

    And the JSON node "data[0].title" should be equal to "Admin task"
    And the JSON node "data[0].creator" should be equal to 1

    And the JSON node "data[1].title" should be equal to "An unassigned task"
    And the JSON node "data[1].creator" should be equal to 1

    And the JSON node "data[2].title" should be equal to "A demo task"
    And the JSON node "data[2].creator" should be equal to 1
