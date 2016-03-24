Feature: /task_comments endpoint
  To CRUD DeskPRO task comments
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: Successfully create a comment
    When I send a POST request to "/api/v2/task_comments" with body:
    """
{
  "comment": "My test comment",
  "task": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    # Should be returned when https://trello.com/c/0q0iVrS9/599-gathered-from-code-add-location-header-in-crud-post is done
    # And the header "Location" should be equal to "/api/v2/task_comments/1"
    And the JSON node "data" should exist
    And the JSON node "data.comment" should be equal to "My test comment"

  Scenario: I GET a single comment
    When I send a GET request to "/api/v2/task_comments/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.comment" should be equal to "My test comment"

  Scenario: I GET comments
    When I send a GET request to "/api/v2/task_comments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination.count" should be equal to 1
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "meta.pagination.total" should be equal to 1
    And the JSON node "data" should exist
    And the JSON node "data[0].comment" should be equal to "My test comment"

  Scenario: I GET comments for a particular task
    When I send a GET request to "/api/v2/tasks/1/comments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination.count" should be equal to 1
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "meta.pagination.total" should be equal to 1
    And the JSON node "data" should exist
    And the JSON node "data[0].comment" should be equal to "My test comment"

  Scenario: I modify a comment
    When I send a PUT request to "/api/v2/task_comments/1" with body:
    """
{
  "comment": "a modified comment"
}
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/task_comments/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.comment" should be equal to "a modified comment"

  Scenario: I try to remove the content from the comment
    When I send a PUT request to "/api/v2/task_comments/1" with body:
    """
{
  "comment": null
}
    """

    Then the response should be in JSON
    And the response status code should be 400

  Scenario: I try to POST a bad comment without a parent task
    When I send a POST request to "/api/v2/task_comments" with body:
    """
{
  "title": "Comment without parent"
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors" should exist
    And the JSON node "errors.fields.task.errors" should exist
    And the JSON node "errors.fields.task.errors[0].code" should be equal to "required"

  Scenario: I DELETE a single task
    When I send a DELETE request to "/api/v2/task_comments/1"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify the resource has been removed by the DELETE request
    When I send a GET request to "/api/v2/task_comments/1"
    Then the response should be in JSON
    And the response status code should be 404
