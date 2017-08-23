@new
Feature: /tasks endpoint
  To CRUD DeskPRO tasks
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And only the following Task records exist:
      | #    | person | title     |
      | task | {me}   | Test Task |

  Scenario: I try to POST a broken task with no title
    When I send a POST request to "/api/v2/tasks/{task}/comments"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.content.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.content.errors[0].message" should be equal to "This value should not be blank."

  Scenario: Successfully create a task comment
    When I send a POST request to "/api/v2/tasks/{task}/comments" with body:
    """
{
  "content": "My test comment"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data" should exist
    And the JSON node "data.content" should be equal to "My test comment"
    And the JSON node "data.person" should be equal to "{me}"
    And the JSON node "data.task" should be equal to "{task}"

  Scenario: I GET a single task comment
    Given only the following TaskComment records exist:
      | #           | person | task   | content           |
      | taskComment | {me}   | {task} | Test Task Comment |
    When I send a GET request to "/api/v2/tasks/{task}/comments/{taskComment}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.content" should be equal to "Test Task Comment"
    And the JSON node "data.person" should be equal to "{me}"
    And the JSON node "data.task" should be equal to "{task}"

  Scenario: I GET task comments
    Given only the following Task records exist:
      | #     | person | title      |
      | task  | {me}   | Test Task  |
      | task2 | {me}   | Test Task 2|
    And only the following TaskComment records exist:
      | #            | person | task    | content             |
      | taskComment  | {me}   | {task}  | Test Task Comment   |
      | taskComment2 | {me}   | {task}  | Test Task Comment 2 |
      | taskComment3 | {me}   | {task2} | Test Task Comment 3 |
      | taskComment4 | {me}   | {task2} | Test Task Comment 4 |
    When I send a GET request to "/api/v2/tasks/{task}/comments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements


  Scenario: I DELETE a single task comment
    Given only the following TaskComment records exist:
      | #           | person | task   | content           |
      | taskComment | {me}   | {task} | Test Task Comment |
    When I send a DELETE request to "/api/v2/tasks/{task}/comments/{taskComment}"
    Then the response should be in JSON
    And the response status code should be 200