Feature: /project_members endpoint
  To CRUD DeskPRO project_members
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated
    And I send a POST request to "/api/v2/projects" with body:
    """
{
  "title": "Test project"
}
    """

  @reinstall
  Scenario: Successfully create a project member
    When I send a POST request to "/api/v2/project_members" with body:
    """
{
  "person": 1,
  "project": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    # And the header "Location" should be equal to "/api/v2/project_members/1"
    And the JSON node "data" should exist
    And the JSON node "data.person" should be equal to "1"

  Scenario: I GET a single member
    When I send a GET request to "/api/v2/project_members/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.person" should be equal to "1"

  Scenario: I modify a member
    When I send a PUT request to "/api/v2/project_members/1" with body:
    """
{
  "team": 1
}
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/project_members/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.team" should be equal to "1"
    And the JSON node "data.person" should exist

  Scenario: I DELETE a single member
    When I send a DELETE request to "/api/v2/project_members/1"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify the resource has been removed by the DELETE request
    When I send a GET request to "/api/v2/project_members/1"
    Then the response should be in JSON
    And the response status code should be 404

  Scenario: I create a person member
    When I send a POST request to "/api/v2/project_members/1/agents" with body:
    """
{
  "id": 1
}
    """
    Then the response status code should be 201
    And the response should be in JSON

  Scenario: I create an agent team member
    When I send a POST request to "/api/v2/project_members/1/teams" with body:
    """
{
  "id": 1
}
    """
    Then the response status code should be 201
    And the response should be in JSON

  Scenario: I create an agent team member that already exists
    When I send a POST request to "/api/v2/project_members/1/teams" with body:
    """
{
  "id": 1
}
    """
    Then the response status code should be 400
    And the response should be in JSON

  Scenario: I create a department member
    When I send a POST request to "/api/v2/project_members/1/departments" with body:
    """
{
  "id": 1
}
    """
    Then the response status code should be 201
    And the response should be in JSON


  Scenario: I verify a person member was added and endpoint works well
    When I send a GET request to "/api/v2/project_members/1/agents"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0]" should exist
    And the JSON node "data[0].id" should be equal to "1"

  Scenario: I verify an agent team member was added and endpoint works well
    When I send a GET request to "/api/v2/project_members/1/teams"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0]" should exist
    And the JSON node "data[0].id" should be equal to "1"

  Scenario: I verify a department was added and endpoint works well
    When I send a GET request to "/api/v2/project_members/1/departments"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0]" should exist
    And the JSON node "data[0].id" should be equal to "1"

  Scenario: I delete an agent member from project
    When I send a DELETE request to "/api/v2/project_members/1/agents/1"
    Then the response should be in JSON
    And the response status code should be 200
    When I send a GET request to "/api/v2/project_members/1/agents"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0]" should not exist

  Scenario: I delete an agent team member from project
    When I send a DELETE request to "/api/v2/project_members/1/teams/1"
    Then the response should be in JSON
    And the response status code should be 200
    When I send a GET request to "/api/v2/project_members/1/teams"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0]" should not exist

  Scenario: I delete an agent member from project
    When I send a DELETE request to "/api/v2/project_members/1/departments/1"
    Then the response should be in JSON
    And the response status code should be 200
    When I send a GET request to "/api/v2/project_members/1/departments"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data[0]" should not exist

  Scenario: I try to create member with malformed request
    When I send a POST request to "/api/v2/project_members/1/departments" with body:
    """

    """
    Then the response status code should be 400