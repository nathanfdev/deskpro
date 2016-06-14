@new
Feature: /tasks/{id}/attachments endpoint
  To CRUD DeskPRO task attachments
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin

  Scenario: Successfully create an attachment
    Given there are no Blob records in the DB
    And I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    And I create a Task and reference it as task
    When I send a POST request to "/api/v2/tasks/{task}/attachments" with body:
    """
{
  "comment": "my text",
  "blob": "AAAAAAAAAAAAAAAAAA"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should match "/\/api\/v2\/tasks\/\d+\/attachments\/\d+/"
    And the JSON node "data.person" should exist
    And the JSON node "data.task" should exist
    And the JSON node "data.comment" should exist
    And the JSON node "data.date_created" should exist

  Scenario: I GET a single attachment
    When I send a GET request to "/api/v2/tasks/{task}/attachments/{lastCreatedId}?include=task_comment"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.blob" should exist
    And the JSON node "data.person" should exist
    And the JSON node "data.task" should exist
    And the JSON node "data.comment" should exist
    And the JSON node "data.date_created" should exist
    And the JSON node "linked.task_comment" should exist

  Scenario: I GET attachments
    When I send a GET request to "/api/v2/tasks/{task}/attachments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination.count" should be equal to 1
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "meta.pagination.total" should be equal to 1
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].blob" should exist
    And the JSON node "data[0].person" should exist
    And the JSON node "data[0].task" should exist
    And the JSON node "data[0].comment" should exist
    And the JSON node "data[0].date_created" should exist

  Scenario: I verify the resource has been updated by the PUT request
    Given I create blob with auth code "BBBBBBBBBBBBBBBBBB"
    When I send a PUT request to "/api/v2/tasks/{task}/attachments/{lastCreatedId}" with body:
    """
{
  "comment": "my text (edited)",
  "blob": "BBBBBBBBBBBBBBBBBB"
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/tasks/{task}/attachments/{lastCreatedId}?include=task_comment"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.blob" should exist
    And the JSON node "data.person" should exist
    And the JSON node "data.task" should exist
    And the JSON node "data.comment" should exist
    And the JSON node "linked.task_comment" should exist

  Scenario: I DELETE a single task attachment
    When I send a DELETE request to "/api/v2/tasks/{task}/attachments/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 200

    When I send a GET request to "/api/v2/tasks/{task}/attachments/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 404

  Scenario: I create a new attachment to test cascades
    Given I create blob with auth code "CCCCCCCCCCCCCCCCCC"
    When I send a POST request to "/api/v2/tasks/{task}/attachments" with body:
    """
{
  "blob": "CCCCCCCCCCCCCCCCCC"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data.blob" should exist
    And the JSON node "data.person" should exist
    And the JSON node "data.task" should exist
    And the JSON node "data.comment" should be equal to 0

  Scenario: I delete the task to test cascades
    When I send a DELETE request to "/api/v2/tasks/{task}"
    Then the response should be in JSON
    And the response status code should be 200

    When I send a GET request to "{lastRequestUrl}"
    Then the response should be in JSON
    And the response status code should be 404
