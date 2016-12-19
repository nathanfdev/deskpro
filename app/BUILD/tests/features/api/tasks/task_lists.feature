@new
Feature: /task_lists endpoint
  To CRUD DeskPRO tasks
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And the following "TaskProject" records exist:
      | #           | title        |
      | taskProject | Test Project |

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
  "project": ~taskProject~
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data.title" should be equal to "Task list 1"
    And the JSON node "data.project" should be equal to "{taskProject}"

  Scenario: I retrieve a list of task lists
    Given the following "TaskList" records exist:
      | project       | title      |
      | {taskProject} | Test List  |
      | {taskProject} | Test List2 |
    When I send a GET request to "/api/v2/task_lists"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data[0].title" should be equal to "Test List2"
    And the JSON node "data[0].project" should be equal to "{taskProject}"
    And the JSON node "data[1].title" should be equal to "Test List"
    And the JSON node "data[1].project" should be equal to "{taskProject}"

  Scenario: I get single task list
    Given the following "TaskList" records exist:
      | #        | project       | title     |
      | taskList | {taskProject} | Test List |
    When I send a GET request to "/api/v2/task_lists/{taskList}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.title" should be equal to "Test List"
    And the JSON node "data.project" should be equal to "{taskProject}"

  Scenario: I modify task list
    Given the following "TaskProject" records exist:
      | #           | title          |
      | taskProject2 | Test Project2 |
    And the following "TaskList" records exist:
      | #        | project       | title     |
      | taskList | {taskProject} | Test List |
    When I send a PUT request to "/api/v2/task_lists/{taskList}" with body:
    """
{
  "title": "Task list (edited)",
  "project": ~taskProject2~
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/task_lists/{taskList}"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.title" should be equal to "Task list (edited)"
    And the JSON node "data.project" should be equal to "{taskProject2}"

  Scenario: I delete task list
    And the following "TaskList" records exist:
      | #        | project       | title     |
      | taskList | {taskProject} | Test List |
    When I send a DELETE request to "/api/v2/task_lists/{taskList}"
    Then the response status code should be 200
