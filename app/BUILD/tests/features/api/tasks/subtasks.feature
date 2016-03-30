Feature: /subtasks endpoint
  To CRUD DeskPRO subtasks
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: Successfully create a subtask
    When I send a POST request to "/api/v2/subtasks" with body:
    """
{
  "title": "My test subtask",
  "task": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    # And the header "Location" should be equal to "/api/v2/subtasks/1"
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test subtask"

  Scenario: I GET a single subtask
    When I send a GET request to "/api/v2/subtasks/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test subtask"
    And the JSON node "data.is_done" should be equal to 0

  Scenario: I GET subtasks
    When I send a GET request to "/api/v2/subtasks"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0].title" should be equal to "My test subtask"

  Scenario: I GET subtasks for a particular task
    When I send a GET request to "/api/v2/tasks/1/subtasks"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0].title" should be equal to "My test subtask"

  Scenario: I modify a subtask
    When I send a PUT request to "/api/v2/subtasks/1" with body:
    """
{
  "is_done": true
}
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/subtasks/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.is_done" should be equal to true

  Scenario: I try to remove the title from the subtask
    When I send a PUT request to "/api/v2/subtasks/1" with body:
    """
{
  "title": null
}
    """

    Then the response should be in JSON
    And the response status code should be 400

  Scenario: I try to POST a bad subtask without a parent task
    When I send a POST request to "/api/v2/subtasks" with body:
    """
{
  "title": "Task without parent"
}
    """
    Then the response should be in JSON
    And the response status code should be 400

  Scenario: I DELETE a single task
    When I send a DELETE request to "/api/v2/subtasks/1"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify the resource has been removed by the DELETE request
    When I send a GET request to "/api/v2/subtasks/1"
    Then the response should be in JSON
    And the response status code should be 404

  Scenario: I POST a second subtask to test cascading deletions
    When I send a POST request to "/api/v2/subtasks" with body:
    """
{
  "title": "Deletion test task",
  "task": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I delete a parent task
    When I send a DELETE request to "/api/v2/tasks/1"
    Then the response should be in JSON
    And the response status code should be 200

#  Scenario: I verify there are no subtasks left
#    When I send a GET request to "/api/v2/subtasks"
#    Then the response should be in JSON
#    And the response status code should be 200
#    And the JSON node "data" should exist
#    And the JSON node "data" should have 0 elements
