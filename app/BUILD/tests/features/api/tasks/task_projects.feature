@new
Feature: /projects endpoint
  To CRUD DeskPRO projects
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And my request is authenticated

  Scenario: Successfully create a project
    When I send a POST request to "/api/v2/task_projects" with body:
    """
{
  "title": "My test project"
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/task_projects/{lastCreatedId}"
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My test project"

  Scenario: I try to make a broken POST request without a title
    When I send a POST request to "/api/v2/task_projects"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I GET a single project
    Given the following "TaskProject" records exist:
      | #  | title        |
      | t1 | Test project |

    When I send a GET request to "/api/v2/task_projects/{t1}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "Test project"

  Scenario: I get task lists
    Given the following "TaskProject" records exist:
      | #  | title        |
      | t1 | Test project |
    When I send a GET request to "/api/v2/task_projects/{t1}/lists"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I GET projects
    Given only the following "TaskProject" records exist:
      | #  | title          |
      | t1 | Test project 1 |
      | t2 | Test project 2 |
      | t3 | Test project 3 |
    When I send a GET request to "/api/v2/task_projects"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0].title" should be equal to "Test project 1"
    And the JSON node "data[1].title" should be equal to "Test project 2"
    And the JSON node "data[2].title" should be equal to "Test project 3"

  Scenario: I modify a project
    Given the following "TaskProject" records exist:
      | #  | title        |
      | t1 | Test project |
    When I send a PUT request to "/api/v2/task_projects/{t1}" with body:
    """
{
  "title": "New project title"
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/task_projects/{t1}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "New project title"

  Scenario: I POST a task to a project
    Given the following "TaskProject" records exist:
      | #  | title        |
      | t1 | Test project |
    When I send a POST request to "/api/v2/tasks" with body:
    """
{
  "title": "Test project task",
  "project": ~t1~
}
    """
    Then the response should be in JSON
    And the response status code should be 201

    When I send a GET request to "/api/v2/task_projects/{t1}/tasks"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data[0].title" should be equal to "Test project task"

  Scenario: I DELETE a single project
    Given the following "TaskProject" records exist:
      | #  | title        |
      | t1 | Test project |
    When I send a DELETE request to "/api/v2/task_projects/{t1}"
    Then the response should be in JSON
    And the response status code should be 200
