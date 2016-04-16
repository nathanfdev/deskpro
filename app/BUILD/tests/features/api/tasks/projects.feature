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
    When I send a POST request to "/api/v2/task_projects" with body:
    """
{
  "title": "My test project"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/task_projects/4"
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test project"

  Scenario: I try to make a broken POST request without a title
    When I send a POST request to "/api/v2/task_projects"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I GET a single project
    When I send a GET request to "/api/v2/task_projects/4"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test project"

  Scenario: I get task lists
    When I send a GET request to "/api/v2/task_projects/4/lists"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I GET projects
    When I send a GET request to "/api/v2/task_projects"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0].title" should be equal to "First project"
    And the JSON node "data[3].title" should be equal to "My test project"

  Scenario: I modify a project
    When I send a PUT request to "/api/v2/task_projects/4" with body:
    """
{
  "title": "New project title"
}
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/task_projects/4"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "New project title"

  Scenario: I POST a task to a project
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Test project task",
  "project": 4
}
    """
    Then the response should be in JSON
    And the response status code should be 201

  Scenario: I verify that the project has tasks attached
    When I send a GET request to "/api/v2/task_projects/4/tasks"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data[0].title" should be equal to "Test project task"

  Scenario: I DELETE a single project
    When I send a DELETE request to "/api/v2/task_projects/4"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify the resource has been removed by the DELETE request
    When I send a GET request to "/api/v2/task_projects/4"
    Then the response should be in JSON
    And the response status code should be 404
