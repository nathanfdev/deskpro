Feature: /projects/{id}/members/departments endpoint
  To CRUD DeskPRO department project_members
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I try to create member with malformed request
    When I send a POST request to "/api/v2/task_projects/1/members/departments"
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "exactly_one_value_should_be_set"
    And the JSON node "errors.errors[0].message" should contain "You should set exactly only one of"

  Scenario: I check validation
    When I send a POST request to "/api/v2/task_projects/1/members/departments" with body:
    """
{
  "person": 1,
  "department": 1
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: person"

  Scenario: I create a department member
    When I send a POST request to "/api/v2/task_projects/1/members/departments" with body:
    """
{
  "department": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.title" should be equal to "sales"

  Scenario: I create an another department member
    When I send a POST request to "/api/v2/task_projects/1/members/departments" with body:
    """
{
  "department": 2
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.title" should be equal to "support"

  Scenario: I try to create a department member with the same id
    When I send a POST request to "/api/v2/task_projects/1/members/departments" with body:
    """
{
  "department": 2
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.department.errors[0].code" should be equal to "unique_entity"
    And the JSON node "errors.fields.department.errors[0].message" should contain "This value already exists in the system."

  Scenario: I create a team member
    When I send a POST request to "/api/v2/task_projects/1/members/teams" with body:
    """
{
  "team": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.name" should be equal to "test team"

  Scenario: I retrieve a list of project members
    When I send a GET request to "/api/v2/task_projects/1/members/departments"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].id" should be equal to 2
    And the JSON node "data[0].title" should be equal to "support"

    And the JSON node "data[1].id" should be equal to 1
    And the JSON node "data[1].title" should be equal to "sales"

  Scenario: I get single project member
    When I send a GET request to "/api/v2/task_projects/1/members/departments/2"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.title" should be equal to "support"

  Scenario: I get a project member tasks
    When I send a GET request to "/api/v2/task_projects/1/members/departments/1/tasks"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I delete a project member
    When I send a DELETE request to "/api/v2/task_projects/1/members/departments/1"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/task_projects/1/members/departments/1"
    Then the response status code should be 404
