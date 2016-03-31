@tasks-nav
Feature: /projects endpoint
  To CRUD DeskPRO projects
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: Successfully create a project
    When I send a POST request to "/api/v2/projects" with body:
    """
{
  "title": "My test project"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    # And the header "Location" should be equal to "/api/v2/projects/1"
    # should be returned when https://trello.com/c/0q0iVrS9/599-gathered-from-code-add-location-header-in-crud-post is done
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test project"

  Scenario: I try to make a broken POST request without a title
    When I send a POST request to "/api/v2/projects" with body:
    """
{
}
    """
    Then the response should be in JSON
    And the response status code should be 400

  Scenario: I GET a single project
    When I send a GET request to "/api/v2/projects/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test project"

  Scenario: I GET projects
    When I send a GET request to "/api/v2/projects"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0].title" should be equal to "My test project"

  Scenario: I modify a project
    When I send a PUT request to "/api/v2/projects/1" with body:
    """
{
  "title": "New project title"
}
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/projects/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "New project title"

  Scenario: I POST a task to a project
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Test project task",
  "project": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I verify that the project has tasks attached
    When I send a GET request to "/api/v2/projects/1/tasks"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data[0].title" should be equal to "Test project task"

  Scenario: I DELETE a single project
    When I send a DELETE request to "/api/v2/projects/1"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify the resource has been removed by the DELETE request
    When I send a GET request to "/api/v2/projects/1"
    Then the response should be in JSON
    And the response status code should be 404