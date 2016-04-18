@tasks
Feature: /task_projects/{id}/members/agents endpoint
  To CRUD DeskPRO person project_members
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I try to create member with malformed request
    When I send a POST request to "/api/v2/task_projects/1/members/agents"
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "exactly_one_value_should_be_set"
    And the JSON node "errors.errors[0].message" should contain "You should set exactly only one of"

  Scenario: I check validation
    When I send a POST request to "/api/v2/task_projects/1/members/agents" with body:
    """
{
  "person": 1,
  "department": 1
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to "Unexpected field names: department"

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

  Scenario: I create an another person member
    When I send a POST request to "/api/v2/task_projects/1/members/agents" with body:
    """
{
  "person": 2
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.primary_email" should be equal to "agent@deskpro.dev"

  Scenario: I try to create a person member with the same id
    When I send a POST request to "/api/v2/task_projects/1/members/agents" with body:
    """
{
  "person": 2
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "unique_entity"
    And the JSON node "errors.fields.person.errors[0].message" should contain "This value already exists in the system."

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
    When I send a GET request to "/api/v2/task_projects/1/members/agents"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].id" should be equal to 2
    And the JSON node "data[0].primary_email" should be equal to "agent@deskpro.dev"

    And the JSON node "data[1].id" should be equal to 1
    And the JSON node "data[1].primary_email" should be equal to "admin@deskpro.dev"

  Scenario: I get single project member
    When I send a GET request to "/api/v2/task_projects/1/members/agents/2?include=usergroup"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.primary_email" should be equal to "agent@deskpro.dev"
    And the JSON node "linked.usergroup.1.title" should be equal to "Everyone"

  Scenario: I get a person project member tasks
    When I send a GET request to "/api/v2/task_projects/1/members/agents/1/tasks"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I delete a project member
    When I send a DELETE request to "/api/v2/task_projects/1/members/agents/1"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/task_projects/1/members/agents/1"
    Then the response status code should be 404
