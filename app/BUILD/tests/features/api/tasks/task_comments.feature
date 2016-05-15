Feature: /tasks/{id}/comments endpoint
  To CRUD DeskPRO task comments
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: Successfully create a comment
    Given I create a task and reference its' ID as taskId
    When I send a POST request to "/api/v2/tasks/{taskId}/comments" with body:
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
    When I send a GET request to "/api/v2/tasks/{taskId}/comments/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.comment" should be equal to "My test comment"

  Scenario: I GET comments
    When I send a GET request to "/api/v2/tasks/{taskId}/comments"
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
    When I send a GET request to "/api/v2/tasks/{taskId}/comments"
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
    When I send a PUT request to "/api/v2/tasks/{taskId}/comments/{lastCreatedId}" with body:
    """
{
  "comment": "a modified comment"
}
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/tasks/{taskId}/comments/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.comment" should be equal to "a modified comment"

  Scenario: I try to remove the content from the comment
    When I send a PUT request to "/api/v2/tasks/{taskId}/comments/{lastCreatedId}" with body:
    """
{
  "comment": null
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.comment.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.comment.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I DELETE a single task
    When I send a DELETE request to "/api/v2/tasks/{taskId}/comments/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify the resource has been removed by the DELETE request
    When I send a GET request to "/api/v2/tasks/{taskId}/comments/{lastCreatedId}"
    Then the response should be in JSON
    And the response status code should be 404
