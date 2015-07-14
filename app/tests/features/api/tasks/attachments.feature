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
    And the JSON node "data.links" should exist
    And the JSON node "data.links.self" should be equal to "/api/v2/task_attachments/1"

  Scenario: I GET a single attachment
    When I send a GET request to "/api/v2/task_attachments/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.blob" should exist
    And the JSON node "data.links.self" should be equal to "/api/v2/task_attachments/1"

  Scenario: I GET attachments
    When I send a GET request to "/api/v2/task_attachments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.count" should be equal to 1
    And the JSON node "meta.page" should be equal to 1
    And the JSON node "meta.total_pages" should be equal to 1
    And the JSON node "meta.total_count" should be equal to 1
    And the JSON node "data" should exist
    And the JSON node "data[0].blob" should exist
    And the JSON node "data[0].links.self" should be equal to "/api/v2/task_attachments/1"

  Scenario: I GET the attachments for a single task
    When I send a GET request to "/api/v2/tasks/1/attachments"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.count" should be equal to 1
    And the JSON node "meta.page" should be equal to 1
    And the JSON node "meta.total_pages" should be equal to 1
    And the JSON node "meta.total_count" should be equal to 1
    And the JSON node "data" should exist
    And the JSON node "data[0].blob" should exist
    And the JSON node "data[0].links.self" should be equal to "/api/v2/task_attachments/1"

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/task_attachments/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.blob" should exist
    And the JSON node "data.links.self" should be equal to "/api/v2/task_attachments/1"

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