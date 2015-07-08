Feature: Tasks
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
    And the header "Location" should equal "/api/v2/tasks/1"
    And the JSON node "data" should exist
    And the JSON node "data.title" should equal "/api/v2/tasks/1"
    And the JSON node "data.links.self" should equal "/api/v2/tasks/1"

  Scenario: I GET a single task
    When I send a GET request to "/api/v2/tasks/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should equal "My test task"
    And the JSON node "data.links.self" should equal "/api/v2/tasks/1"
