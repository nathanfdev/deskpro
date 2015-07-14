Feature: /tasks endpoint
  To CRUD DeskPRO tasks
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: Successfully create a task
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "My test task"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/tasks/2"
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test task"
    And the JSON node "data.links" should exist
    And the JSON node "data.links.self" should be equal to "/api/v2/tasks/2"

  Scenario: I GET a single task
    When I send a GET request to "/api/v2/tasks/2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test task"
    And the JSON node "data.links.self" should be equal to "/api/v2/tasks/2"

  Scenario: I GET tasks
    When I send a GET request to "/api/v2/tasks"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.count" should be equal to 2
    And the JSON node "meta.page" should be equal to 1
    And the JSON node "meta.total_pages" should be equal to 1
    And the JSON node "meta.total_count" should be equal to 2
    And the JSON node "data" should exist
    And the JSON node "data[1].title" should be equal to "My test task"
    And the JSON node "data[1].links.self" should be equal to "/api/v2/tasks/2"

  Scenario: I modify a task
    When I send a PUT request to "/api/v2/tasks/2" with body:
    """
{
  "title": "New task title"
}
    """
    Then the response should be in JSON
    And the response status code should be 204
    And the response should be empty

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/tasks/2"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "New task title"
    And the JSON node "data.links.self" should be equal to "/api/v2/tasks/2"

  Scenario: I DELETE a single task
    When I send a DELETE request to "/api/v2/tasks/2"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify the resource has been removed by the DELETE request
    When I send a GET request to "/api/v2/tasks/2"
    Then the response should be in JSON
    And the response status code should be 404