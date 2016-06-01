Feature: /tasks/{id}/subtasks endpoint
  To CRUD DeskPRO subtasks
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

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
    And the JSON node "data.creator" should be equal to 1

  Scenario: I GET a single subtask
    When I send a GET request to "/api/v2/tasks/{task}/subtasks/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test subtask"
    And the JSON node "data.is_done" should be equal to 0

  Scenario: I GET subtasks
    When I send a GET request to "/api/v2/tasks/{task}/subtasks"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0].title" should be equal to "My test subtask"

  Scenario: I modify a subtask
    When I send a PUT request to "/api/v2/tasks/{task}/subtasks/{lastCreatedId}" with body:
    """
{
  "is_done": true
}
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/tasks/{task}/subtasks/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.is_done" should be equal to true

  Scenario: I try to remove the title from the subtask
    When I send a PUT request to "/api/v2/tasks/{task}/subtasks/{lastCreatedId}" with body:
    """
{
  "title": null
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I DELETE a single task
    When I send a DELETE request to "/api/v2/tasks/{task}/subtasks/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify the resource has been removed by the DELETE request
    When I send a GET request to "/api/v2/tasks/{task}/subtasks/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 404

  Scenario: I POST a second subtask to test cascading deletions
    When I send a POST request to "/api/v2/tasks/{task}/subtasks" with body:
    """
{
  "title": "Deletion test task"
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I delete a parent task
    When I send a DELETE request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify there are no subtasks left
    When I send a GET request to "{lastRequestUrl}"
    Then the response should be in JSON
    And the response status code should be 404