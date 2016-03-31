Feature: /project_members endpoint
  To CRUD DeskPRO project_members
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I create a new project
    And I send a POST request to "/api/v2/task_projects" with body:
    """
{
  "title": "Test project"
}
    """
    And the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.title" should be equal to "Test project"

  Scenario: I try to create member with malformed request
    When I send a POST request to "/api/v2/task_projects/1/members"
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "exactly_one_value_should_be_set"
    And the JSON node "errors.errors[0].message" should contain "You should set exactly only one of"

  Scenario: I check validation
    When I send a POST request to "/api/v2/task_projects/1/members" with body:
    """
{
  "person": 1,
  "department": 1
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "exactly_one_value_should_be_set"
    And the JSON node "errors.errors[0].message" should contain "You should set exactly only one of"

  Scenario: I create a person member
    When I send a POST request to "/api/v2/task_projects/1/members" with body:
    """
{
  "person": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.person" should be equal to 1


  Scenario: I try to create a person member with the same id
    When I send a POST request to "/api/v2/task_projects/1/members" with body:
    """
{
  "person": 1
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "unique_entity"
    And the JSON node "errors.fields.person.errors[0].message" should contain "This value already exists in the system."

  Scenario: I create a team member
    When I send a POST request to "/api/v2/task_projects/1/members" with body:
    """
{
  "team": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.team" should be equal to 1

  Scenario: I try to create a team member with the same id
    When I send a POST request to "/api/v2/task_projects/1/members" with body:
    """
{
  "team": 1
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.team.errors[0].code" should be equal to "unique_entity"
    And the JSON node "errors.fields.team.errors[0].message" should contain "This value already exists in the system."

  Scenario: I create a department member
    When I send a POST request to "/api/v2/task_projects/1/members" with body:
    """
{
  "department": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.department" should be equal to 1

  Scenario: I try to create a department member with the same id
    When I send a POST request to "/api/v2/task_projects/1/members" with body:
    """
{
  "department": 1
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.department.errors[0].code" should be equal to "unique_entity"
    And the JSON node "errors.fields.department.errors[0].message" should contain "This value already exists in the system."

  Scenario: I retrieve a list of project members
    When I send a GET request to "/api/v2/task_projects/1/members"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements

    And the JSON node "data[0].id" should be equal to 3
    And the JSON node "data[0].project" should be equal to 1
    And the JSON node "data[0].department" should be equal to 1
    And the JSON node "data[0].team" should be equal to 0
    And the JSON node "data[0].person" should be equal to 0

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].project" should be equal to 1
    And the JSON node "data[1].team" should be equal to 1
    And the JSON node "data[1].department" should be equal to 0
    And the JSON node "data[1].person" should be equal to 0

    And the JSON node "data[2].id" should be equal to 1
    And the JSON node "data[2].project" should be equal to 1
    And the JSON node "data[2].person" should be equal to 1
    And the JSON node "data[2].department" should be equal to 0
    And the JSON node "data[2].team" should be equal to 0

  Scenario: I filter by person
    When I send a GET request to "/api/v2/task_projects/1/members?type=person"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1

  Scenario: I filter by team
    When I send a GET request to "/api/v2/task_projects/1/members?type=team"
    And print last JSON response
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 2

  Scenario: I filter by person
    When I send a GET request to "/api/v2/task_projects/1/members?type=department"
    And print last JSON response
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 3

  Scenario: I retrieve a list of project members with sideloading
    When I send a GET request to "/api/v2/task_projects/1/members?include=person,agent_team,department"
    Then the response status code should be 200
    And the JSON node "linked.agent_team.1.id" should be equal to 1
    And the JSON node "linked.person.1.id" should be equal to 1
    And the JSON node "linked.department.1.id" should be equal to 1

  Scenario: I get single member
    When I send a GET request to "/api/v2/task_projects/1/members/1"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.person" should be equal to 1

  Scenario: I get a person project member tasks
    When I send a GET request to "/api/v2/task_projects/1/members/1/tasks"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I get a team project member tasks
    When I send a GET request to "/api/v2/task_projects/1/members/2/tasks"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I get a department project member tasks
    When I send a GET request to "/api/v2/task_projects/1/members/3/tasks"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I modify a project member
    When I send a PUT request to "/api/v2/task_projects/1/members/1" with body:
        """
{
  "department": 2
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/task_projects/1/members/1"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.person" should be equal to 0
    And the JSON node "data.department" should be equal to 2

  Scenario: I delete a project member
    When I send a DELETE request to "/api/v2/task_projects/1/members/1"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/task_projects/1/members/1"
    Then the response status code should be 404
