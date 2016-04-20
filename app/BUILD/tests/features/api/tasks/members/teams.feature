@tasks
Feature: /task_projects/{id}/members/teams endpoint
  To CRUD DeskPRO team project_members
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I try to create member with malformed request
    When I send a POST request to "/api/v2/task_projects/1/members/teams"
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "exactly_one_value_should_be_set"
    And the JSON node "errors.errors[0].message" should contain "You should set exactly only one of"

  Scenario: I check validation
    When I send a POST request to "/api/v2/task_projects/1/members/teams" with body:
    """
{
  "team": 1,
  "department": 1
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: department"

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

  Scenario: I create an another team member
    When I send a POST request to "/api/v2/task_projects/1/members/teams" with body:
    """
{
  "team": 2
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.name" should be equal to "Support Managers"

  Scenario: I try to create a team member with the same id
    When I send a POST request to "/api/v2/task_projects/1/members/teams" with body:
    """
{
  "team": 2
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.team.errors[0].code" should be equal to "unique_entity"
    And the JSON node "errors.fields.team.errors[0].message" should contain "This value already exists in the system."

  Scenario: I create a person member
    When I send a POST request to "/api/v2/task_projects/1/members/agents" with body:
    """
{
  "person": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.primary_email" should be equal to "admin@deskpro.dev"

  Scenario: I retrieve a list of project members
    When I send a GET request to "/api/v2/task_projects/1/members/teams"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].id" should be equal to 2
    And the JSON node "data[0].name" should be equal to "Support Managers"

    And the JSON node "data[1].id" should be equal to 1
    And the JSON node "data[1].name" should be equal to "test team"

  Scenario: I get single project member
    When I send a GET request to "/api/v2/task_projects/1/members/teams/2"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.name" should be equal to "Support Managers"

  Scenario: I get a project member tasks
    When I send a GET request to "/api/v2/task_projects/1/members/teams/1/tasks"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I delete a project member
    When I send a DELETE request to "/api/v2/task_projects/1/members/teams/1"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/task_projects/1/members/teams/1"
    Then the response status code should be 404