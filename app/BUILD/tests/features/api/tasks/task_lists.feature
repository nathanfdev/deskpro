@tasks
Feature: /task_lists endpoint
  To CRUD DeskPRO tasks
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I try to create a new task list with empty request
    When I send a POST request to "/api/v2/task_lists"
    Then the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.project.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.project.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a new task list
    When I send a POST request to "/api/v2/task_lists" with body:
    """
{
  "title": "Task list 1",
  "project": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data.title" should be equal to "Task list 1"
    And the JSON node "data.project" should be equal to 1

  Scenario: I retrieve a list of task lists
    When I send a GET request to "/api/v2/task_lists"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data[0].title" should be equal to "Task list 1"
    And the JSON node "data[0].project" should be equal to 1

  Scenario: I get single task list
    When I send a GET request to "/api/v2/task_lists/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.title" should be equal to "Task list 1"
    And the JSON node "data.project" should be equal to 1

  Scenario: I modify task list
    When I send a PUT request to "/api/v2/task_lists/1" with body:
    """
{
  "title": "Task list 1 (edited)",
  "project": 2
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/task_lists/1"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.title" should be equal to "Task list 1 (edited)"
    And the JSON node "data.project" should be equal to 2

  Scenario: I delete task list
    When I send a DELETE request to "/api/v2/task_lists/1"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/task_lists/1"
    Then the response status code should be 404
