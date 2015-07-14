Feature: /project_members endpoint
  To CRUD DeskPRO project_members
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: Successfully create a project member
    When I send a POST request to "/api/v2/project_members" with body:
    """
{
  "person": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/project_members/1"
    And the JSON node "data" should exist
    And the JSON node "data.person.id" should be equal to "1"
    And the JSON node "data.links" should exist
    And the JSON node "data.links.self" should be equal to "/api/v2/project_members/1"

  Scenario: I GET a single member
    When I send a GET request to "/api/v2/project_members/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.person.id" should be equal to "1"
    And the JSON node "data.links.self" should be equal to "/api/v2/project_members/1"

  Scenario: I GET project members
    When I send a GET request to "/api/v2/project_members"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.count" should be equal to 1
    And the JSON node "meta.page" should be equal to 1
    And the JSON node "meta.total_pages" should be equal to 1
    And the JSON node "meta.total_count" should be equal to 1
    And the JSON node "data" should exist
    And the JSON node "data[0].person.id" should be equal to "1"
    And the JSON node "data[0].links.self" should be equal to "/api/v2/project_members/1"

  Scenario: I modify a member
    When I send a PUT request to "/api/v2/project_members/1" with body:
    """
{
  "team": 1
}
    """
    Then the response should be in JSON
    And the response status code should be 204
    And the response should be empty

  Scenario: I verify the resource has been updated by the PUT request
    When I send a GET request to "/api/v2/project_members/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.team.id" should be equal to "1"
    And the JSON node "data.person" should not exist
    And the JSON node "data.links.self" should be equal to "/api/v2/project_members/1"

  Scenario: I DELETE a single task
    When I send a DELETE request to "/api/v2/project_members/1"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I verify the resource has been removed by the DELETE request
    When I send a GET request to "/api/v2/project_members/1"
    Then the response should be in JSON
    And the response status code should be 404