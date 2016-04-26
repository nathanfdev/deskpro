@tasks
Feature: /tasks endpoint
  To CRUD DeskPRO tasks
  As a developer
  I want to check project filters

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I prepare tasks
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Task without project"
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Task with project 2",
  "project": 2
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Task with project 1",
  "project": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Another task with project 2",
  "project": 2
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I filter w/o project
    When I send a GET request to "/api/v2/tasks?order_by=project"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 6 elements
    And the JSON node "data[0].project" should be equal to 0
    And the JSON node "data[1].project" should be equal to 0
    And the JSON node "data[2].project" should be equal to 0
    And the JSON node "data[3].project" should be equal to 1
    And the JSON node "data[4].project" should be equal to 2
    And the JSON node "data[5].project" should be equal to 2

  Scenario: I filter by first project
    When I send a GET request to "/api/v2/tasks?project=1&order_by=project"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].title" should be equal to "Task with project 1"
    And the JSON node "data[0].project" should be equal to 1

  Scenario: I filter by second project
    When I send a GET request to "/api/v2/tasks?project=2&order_by=project"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements

  Scenario: I filter by any project
    When I send a GET request to "/api/v2/tasks?project[0]=1&project[1]=2&order_by=project&order_dir=desc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 3 elements

    And the JSON node "data[0].project" should be equal to 2
    And the JSON node "data[1].project" should be equal to 2
    And the JSON node "data[2].project" should be equal to 1
