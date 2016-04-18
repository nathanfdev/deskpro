@tasks
Feature: /tasks endpoint
  To CRUD DeskPRO tasks
  As a developer
  I want to check is_done filters

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I prepare tasks
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Not completed task"
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Completed task",
  "is_done": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I don't filter tasks
    When I send a GET request to "/api/v2/tasks"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 4 elements

    And the JSON node "data[0].title" should be equal to "Completed task"
    And the JSON node "data[0].is_done" should be equal to 1

    And the JSON node "data[1].title" should be equal to "Not completed task"
    And the JSON node "data[1].is_done" should be equal to 0

    And the JSON node "data[2].title" should be equal to "An unassigned task"
    And the JSON node "data[2].is_done" should be equal to 0

    And the JSON node "data[3].title" should be equal to "A demo task"
    And the JSON node "data[3].is_done" should be equal to 0

  Scenario: I filter not completed tasks
    When I send a GET request to "/api/v2/tasks?done=0"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 3 elements

    And the JSON node "data[0].title" should be equal to "Not completed task"
    And the JSON node "data[0].is_done" should be equal to 0

    And the JSON node "data[1].title" should be equal to "An unassigned task"
    And the JSON node "data[1].is_done" should be equal to 0

    And the JSON node "data[2].title" should be equal to "A demo task"
    And the JSON node "data[2].is_done" should be equal to 0

  Scenario: I filter completed tasks
    When I send a GET request to "/api/v2/tasks?done=1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element

    And the JSON node "data[0].title" should be equal to "Completed task"
    And the JSON node "data[0].is_done" should be equal to 1
