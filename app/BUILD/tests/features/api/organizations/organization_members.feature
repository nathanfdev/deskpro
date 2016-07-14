@new
Feature: /organizations/{id}/members endpoint
  To CRUD DeskPRO organization members
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And "user@deskpro.dev" user exists
    And only the following "Organization" records exist:
      | #             | name          |
      | organization  | Organization1 |
      | organization2 | Organization2 |

  Scenario: I retrieve list of organization members
    Given the "user" is in "organization" organization
    And the "admin" is in "organization" organization
    When I send a GET request to "/api/v2/organizations/{organization}/members"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{user}"
    And the JSON node "data[0].primary_email" should be equal to "user@deskpro.dev"
    And the JSON node "data[1].id" should be equal to "{admin}"
    And the JSON node "data[1].primary_email" should be equal to "admin@deskpro.dev"


  Scenario: I try add a new member with empty request
    Given the "user" has no organization
    When I send a POST request to "/api/v2/organizations/{organization}/members"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.person.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I try to add not existing person
    When I send a POST request to "/api/v2/organizations/{organization}/members" with body:
    """
{
  "person": 404,
  "position": "some text"
}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.person.errors[0].message" should be equal to "One or more of the given values is invalid."

  Scenario: I try add a member who is already in organization
    Given the "user" is in "organization" organization
    When I send a POST request to "/api/v2/organizations/{organization}/members" with body:
    """
{
  "person": ~user~,
  "position": "some text"
}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "already_in_organization"
    And the JSON node "errors.fields.person.errors[0].message" should be equal to "That user is already in an organization."

  Scenario: I remove person organization and re try to add
    When I send a PUT request to "/api/v2/people/{user}" with body:
    """
{
  "organization": null
}
    """
    Then the response status code should be 204

    When I send a POST request to "/api/v2/organizations/{organization}/members" with body:
    """
{
  "person": ~user~,
  "position": "some text"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{user}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.id" should be equal to "{user}"
    And the JSON node "data.organization" should be equal to "{organization}"
    And the JSON node "data.organization_position" should be equal to "some text"

  Scenario: I reset organization position
    Given the "user" is in "organization2" organization
    When I send a POST request to "/api/v2/organizations/{organization}/members" with body:
    """
{
  "person": ~user~,
  "position": ""
}
    """
    When I send a GET request to "/api/v2/people/{user}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.id" should be equal to "{user}"
    And the JSON node "data.organization" should be equal to "{organization2}"
    And the JSON node "data.organization_position" should be equal to 0

  Scenario: I try to remove person from another organization
    When I send a DELETE request to "/api/v2/organizations/{organization2}/members/{user}"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "message" should be equal to "Person is not a member of this organization."

  Scenario: I remove person from organization
    Given the "user" is in "organization" organization
    When I send a DELETE request to "/api/v2/organizations/{organization}/members/{user}"
    Then the response should be in JSON
    And the response status code should be 200

    When I send a GET request to "/api/v2/people/{user}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.id" should be equal to "{user}"
    And the JSON node "data.organization" should be equal to 0
    And the JSON node "data.organization_position" should be equal to 0
