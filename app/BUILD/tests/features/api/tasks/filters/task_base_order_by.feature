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
    When I send a POST request to "/api/v2/task_lists" with body:
    """
{
  "title": "Task list 1",
  "project": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/task_lists" with body:
    """
{
  "title": "Task list 2",
  "project": 2
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "List 1 task",
  "list": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "List 2 task",
  "list": 2
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Another list 2 task",
  "list": 2
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I order by id
    When I send a GET request to "/api/v2/tasks?order_by=id&order_dir=desc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 5 elements
    And the JSON node "data[0].id" should be equal to 5
    And the JSON node "data[1].id" should be equal to 4
    And the JSON node "data[2].id" should be equal to 3
    And the JSON node "data[3].id" should be equal to 2
    And the JSON node "data[4].id" should be equal to 1

    When I send a GET request to "/api/v2/tasks?order_by=id&order_dir=asc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 5 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[2].id" should be equal to 3
    And the JSON node "data[3].id" should be equal to 4
    And the JSON node "data[4].id" should be equal to 5

  Scenario: I order by title
    When I send a GET request to "/api/v2/tasks?order_by=title&order_dir=desc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 5 elements
    And the JSON node "data[0].title" should be equal to "List 2 task"
    And the JSON node "data[1].title" should be equal to "List 1 task"
    And the JSON node "data[2].title" should be equal to "Another list 2 task"
    And the JSON node "data[3].title" should be equal to "An unassigned task"
    And the JSON node "data[4].title" should be equal to "A demo task"

    When I send a GET request to "/api/v2/tasks?order_by=title&order_dir=asc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 5 elements
    And the JSON node "data[0].title" should be equal to "A demo task"
    And the JSON node "data[1].title" should be equal to "An unassigned task"
    And the JSON node "data[2].title" should be equal to "Another list 2 task"
    And the JSON node "data[3].title" should be equal to "List 1 task"
    And the JSON node "data[4].title" should be equal to "List 2 task"

  Scenario: I order by list
    When I send a GET request to "/api/v2/tasks?order_by=list&order_dir=desc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 5 elements
    And the JSON node "data[0].title" should be equal to "List 2 task"
    And the JSON node "data[1].title" should be equal to "Another list 2 task"
    And the JSON node "data[2].title" should be equal to "List 1 task"
    And the JSON node "data[3].title" should be equal to "A demo task"
    And the JSON node "data[4].title" should be equal to "An unassigned task"

    When I send a GET request to "/api/v2/tasks?order_by=list&order_dir=asc"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 5 elements
    And the JSON node "data[0].title" should be equal to "A demo task"
    And the JSON node "data[1].title" should be equal to "An unassigned task"
    And the JSON node "data[2].title" should be equal to "List 1 task"
    And the JSON node "data[3].title" should be equal to "List 2 task"
    And the JSON node "data[4].title" should be equal to "Another list 2 task"

  Scenario: I check wrong order dir
    When I send a GET request to "/api/v2/tasks?order_by=list&order_dir=unknown"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "message" should be equal to "Unknown order value"

    When I send a GET request to "/api/v2/tasks?order_by=id&order_dir=unknown"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "message" should be equal to "Unknown order value"
