@new
Feature: /tasks/{id}/comments endpoint
  To CRUD DeskPRO task comments
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And the following Task records exist:
      | #    | creator | title     |
      | task | {me}    | Test Task |
    And the following "TaskComment" records exist:
      | #        | task   | comment | person |
      | comment1 | {task} | test    | {me}   |
      | comment2 | {task} | test    | {me}   |
  Scenario: Successfully create a comment

    When I send a POST request to "/api/v2/tasks/{task}/comments" with body:
    """
{
  "comment": "My test comment"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should match "/\/api\/v2\/tasks\/\d+\/comments\/\d+/"
    And the JSON node "data" should exist
    And the JSON node "data.comment" should be equal to "My test comment"

  Scenario: I GET a single comment
    When I send a GET request to "/api/v2/tasks/{task}/comments/{comment1}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.comment" should be equal to "test"

  Scenario: I GET comments
    When I send a GET request to "/api/v2/tasks/{task}/comments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination.count" should be equal to 2
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "meta.pagination.total" should be equal to 2
    And the JSON node "data" should exist
    And the JSON node "data[0].id" should be equal to "{comment1}"

  Scenario: I modify a comment
    When I send a PUT request to "/api/v2/tasks/{task}/comments/{comment1}" with body:
    """
{
  "comment": "a modified comment"
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/tasks/{task}/comments/{comment1}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.comment" should be equal to "a modified comment"

  Scenario: I try to remove the content from the comment

    When I send a PUT request to "/api/v2/tasks/{task}/comments/{comment1}" with body:
    """
{
  "comment": null
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.comment.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.comment.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I DELETE a single comment
    When I send a DELETE request to "/api/v2/tasks/{task}/comments/{comment1}"
    Then the response should be in JSON
    And the response status code should be 200
