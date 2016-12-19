@new
Feature: /tasks/{id}/subtasks endpoint
  To CRUD DeskPRO subtasks
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And the following "Task" records exist:
      | #    | creator | title     |
      | task | {me}    | Test Task |

  Scenario: Successfully create a subtask
    Given I create a Task and reference it as task
    When I send a POST request to "/api/v2/tasks/{task}/subtasks" with body:
    """
{
  "title": "My test subtask"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/tasks/{task}/subtasks/{lastCreatedId}"
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test subtask"
    And the JSON node "data.creator" should be equal to "{me}"

  Scenario: I GET a single subtask
    Given the following "TaskSubtask" records exist:
      | #       | task   | title        | creator |
      | subtask | {task} | Test Subtask | {me}    |
    When I send a GET request to "/api/v2/tasks/{task}/subtasks/{subtask}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "Test Subtask"
    And the JSON node "data.is_done" should be equal to 0

  Scenario: I GET subtasks
    Given the following "TaskSubtask" records exist:
      | #       | task   | title        | creator |
      | subtask | {task} | Test Subtask | {me}    |
    When I send a GET request to "/api/v2/tasks/{task}/subtasks"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0].title" should be equal to "Test Subtask"

  Scenario: I modify a subtask
    Given the following "TaskSubtask" records exist:
      | #       | task   | title        | creator |
      | subtask | {task} | Test Subtask | {me}    |
    When I send a PUT request to "/api/v2/tasks/{task}/subtasks/{subtask}" with body:
    """
{
  "is_done": true
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/tasks/{task}/subtasks/{subtask}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.is_done" should be equal to true

  Scenario: I try to remove the title from the subtask
    Given the following "TaskSubtask" records exist:
      | #       | task   | title        | creator |
      | subtask | {task} | Test Subtask | {me}    |
    When I send a PUT request to "/api/v2/tasks/{task}/subtasks/{subtask}" with body:
    """
{
  "title": null
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I DELETE a single subtask
    Given the following "TaskSubtask" records exist:
      | #       | task   | title        | creator |
      | subtask | {task} | Test Subtask | {me}    |
    When I send a DELETE request to "/api/v2/tasks/{task}/subtasks/{subtask}"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I POST a second subtask to test cascading deletions
    Given the following "TaskSubtask" records exist:
      | #       | task   | title        | creator |
      | subtask | {task} | Test Subtask | {me}    |

    When I send a DELETE request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200

    When I send a GET request to "{lastRequestUrl}"
    Then the response should be in JSON
    And the response status code should be 404