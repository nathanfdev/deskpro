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
    When I send a POST request to "/api/v2/tasks"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."

  Scenario: Successfully create a task
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "My test task"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test task"

  Scenario: Successfully create a task with tickets associated
    Given only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
      | t2 | Ticket 2 |
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "My test task with tickets",
  "tickets": [~t1~, ~t2~]
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test task with tickets"

  Scenario: I GET a single task
    When I send a GET request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "Test Task"

  Scenario: I GET tasks
    When I send a GET request to "/api/v2/tasks"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I modify a task
    When I send a PUT request to "/api/v2/tasks/{task}" with body:
    """
{
  "title": "New task title"
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "New task title"

  Scenario: I add labels to a task
    When I send a PUT request to "/api/v2/tasks/{task}" with body:
    """
{
  "labels": ["c label", "z label", "a label"]
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.labels" should exist
    And the JSON node "data.labels[0]" should be equal to "a label"
    And the JSON node "data.labels[2]" should be equal to "z label"

  Scenario: I DELETE a single task
    When I send a DELETE request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200