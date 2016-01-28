Feature: /task_attachments endpoint
  To CRUD DeskPRO task attachments
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: Successfully create an attachment
    When I send a POST request to "/api/v2/task_attachments" with body:
    """
{
  "file": "VGhpcyBpcyBhIHRlc3Q=",
  "filename": "test.txt",
  "content_type": "text/plain",
  "task": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/task_attachments/1"
    And the JSON node "data" should exist
    And the JSON node "data.blob" should exist

  Scenario: I GET a single attachment
    When I send a GET request to "/api/v2/task_attachments/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.blob" should exist

  Scenario: I GET attachments
    When I send a GET request to "/api/v2/task_attachments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination.count" should be equal to 1
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "meta.pagination.total" should be equal to 1
    And the JSON node "data" should exist
    And the JSON node "data[0].blob" should exist

  Scenario: I GET the attachments for a single task
    When I send a GET request to "/api/v2/tasks/1/attachments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination.count" should be equal to 1
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "meta.pagination.total" should be equal to 1
    And the JSON node "data" should exist
    And the JSON node "data[0].blob" should exist

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/task_attachments/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.blob" should exist

  Scenario: I try to POST a bad attachment without a parent task
    When I send a POST request to "/api/v2/task_attachments" with body:
    """
{
  "file": "VGhpcyBpcyBhIHRlc3Q=",
  "filename": "test.txt",
  "content_type": "text/plain",
}
    """
    Then the response should be in JSON
    And the response status code should be 400

  Scenario: I DELETE a single task
    When I send a DELETE request to "/api/v2/task_attachments/1"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify the resource has been removed by the DELETE request
    When I send a GET request to "/api/v2/task_attachments/1"
    Then the response should be in JSON
    And the response status code should be 404

  Scenario: I create a new attachment to test cascades
    When I send a POST request to "/api/v2/task_attachments" with body:
    """
{
  "file": "VGhpcyBpcyBhIHRlc3Q=",
  "filename": "test.txt",
  "content_type": "text/plain",
  "task": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I create a comment to delete
    When I send a POST request to "/api/v2/task_comments" with body:
    """
{
  "comment": "Hello world",
  "task": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I create another new attachment to test comment cascades
    When I send a POST request to "/api/v2/task_attachments" with body:
    """
{
  "file": "VGhpcyBpcyBhIHRlc3Q=",
  "filename": "test2.txt",
  "content_type": "text/plain",
  "task": 1,
  "comment": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I check everything has been attached to the task
    When I send a GET request to "/api/v2/tasks/1/attachments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 2 elements
    And the JSON node "data[1].comment" should exist

  Scenario: I delete the comment to test cascades
    When I send a DELETE request to "/api/v2/task_comments/1"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify there is only one attachment left for the task
    When I send a GET request to "/api/v2/tasks/1/attachments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data" should have 1 element

  Scenario: I delete the task to test cascades
    When I send a DELETE request to "/api/v2/tasks/1"
    Then the response should be in JSON
    And the response status code should be 200

#  Disabled pending serializer fix
#  Scenario: I verify there are no attachments left
#    When I send a GET request to "/api/v2/task_attachments"
#    Then the response should be in JSON
#    And the response status code should be 200
#    And the JSON node "data" should exist
#    And the JSON node "data" should have 0 elements