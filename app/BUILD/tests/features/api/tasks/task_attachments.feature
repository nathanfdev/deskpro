@tasks
Feature: /tasks/{id}/attachments endpoint
  To CRUD DeskPRO task attachments
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: Successfully create an attachment
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    When I send a POST request to "/api/v2/tasks/1/attachments" with body:
    """
{
  "comment": "my text",
  "blob": "AAAAAAAAAAAAAAAAAA"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/tasks/1/attachments/1"
    And the JSON node "data.blob" should be equal to 1
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.task" should be equal to 1
    And the JSON node "data.comment" should be equal to 1
    And the JSON node "data.date_created" should exist

  Scenario: I GET a single attachment
    When I send a GET request to "/api/v2/tasks/1/attachments/1?include=task_comment"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.blob" should be equal to 1
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.task" should be equal to 1
    And the JSON node "data.comment" should be equal to 1
    And the JSON node "data.date_created" should exist

    And the JSON node "linked.task_comment.1.id" should be equal to 1
    And the JSON node "linked.task_comment.1.person" should be equal to 1
    And the JSON node "linked.task_comment.1.task" should be equal to 1
    And the JSON node "linked.task_comment.1.comment" should be equal to "my text"

  Scenario: I GET attachments
    When I send a GET request to "/api/v2/tasks/1/attachments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination.count" should be equal to 1
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "meta.pagination.total" should be equal to 1
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].blob" should be equal to 1
    And the JSON node "data[0].person" should be equal to 1
    And the JSON node "data[0].task" should be equal to 1
    And the JSON node "data[0].comment" should be equal to 1
    And the JSON node "data[0].date_created" should exist

  Scenario: I verify the resource has been updated by the PUT request
    Given I create blob with auth code "BBBBBBBBBBBBBBBBBB"
    When I send a PUT request to "/api/v2/tasks/1/attachments/1" with body:
    """
{
  "comment": "my text (edited)",
  "blob": "BBBBBBBBBBBBBBBBBB"
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/tasks/1/attachments/1?include=task_comment"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.blob" should be equal to 2
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.task" should be equal to 1
    And the JSON node "data.comment" should be equal to 1

    And the JSON node "linked.task_comment.1.id" should be equal to 1
    And the JSON node "linked.task_comment.1.person" should be equal to 1
    And the JSON node "linked.task_comment.1.task" should be equal to 1
    And the JSON node "linked.task_comment.1.comment" should be equal to "my text (edited)"

  Scenario: I DELETE a single task attachment
    When I send a DELETE request to "/api/v2/tasks/1/attachments/1"
    Then the response should be in JSON
    And the response status code should be 200

    When I send a GET request to "/api/v2/tasks/1/attachments/1"
    Then the response should be in JSON
    And the response status code should be 404

  Scenario: I create a new attachment to test cascades
    Given I create blob with auth code "CCCCCCCCCCCCCCCCCC"
    When I send a POST request to "/api/v2/tasks/1/attachments" with body:
    """
{
  "blob": "CCCCCCCCCCCCCCCCCC"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data.blob" should be equal to 3
    And the JSON node "data.person" should be equal to 1
    And the JSON node "data.task" should be equal to 1
    And the JSON node "data.comment" should be equal to 0

  Scenario: I delete the task to test cascades
    When I send a DELETE request to "/api/v2/tasks/1"
    Then the response should be in JSON
    And the response status code should be 200

    When I send a GET request to "/api/v2/tasks/1/attachments"
    Then the response should be in JSON
    And the response status code should be 404
