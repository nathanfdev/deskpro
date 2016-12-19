@new
Feature: /organization_notes endpoint
  To CRUD DeskPRO organization notes
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"
    And only the following "Organization" records exist:
      | #    | name       | summary                                    |
      | org1 | Vector ltd | Vector is a common fake org name in Russia |

  Scenario: I try to get notes of not existed organization
    When I send a GET request to "/api/v2/organizations/404/notes"
    Then the response status code should be 404

  Scenario: I try to create a note with empty request
    When I send a POST request to "/api/v2/organizations/{org1}/notes"
    Then the response status code should be 400
    And the JSON node "errors.fields.note.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.note.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a note
    When I send a POST request to "/api/v2/organizations/{org1}/notes" with body:
    """
{
  "note": "my note"
}
    """
    Then the response status code should be 201
    And the JSON node "data.note" should be equal to "my note"
    And the JSON node "data.agent" should be equal to "{me}"
    And the JSON node "data.organization" should be equal to "{org1}"

  Scenario: I retrieve list of organization notes
    Given only the following "OrganizationNote" records exist:
      | #  | organization | agent | note    |
      | n1 | {org1}       | {me}  | my note |
    When I send a GET request to "/api/v2/organizations/{org1}/notes"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{n1}"
    And the JSON node "data[0].note" should be equal to "my note"
    And the JSON node "data[0].agent" should be equal to "{me}"
    And the JSON node "data[0].organization" should be equal to "{org1}"

  Scenario: I modify a note
    Given only the following "OrganizationNote" records exist:
      | #  | organization | agent | note    |
      | n1 | {org1}       | {me}  | my note |
    When I send a PUT request to "/api/v2/organizations/{org1}/notes/{n1}" with body:
    """
{
  "note": "my note (edited)"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/organizations/{org1}/notes/{n1}"
    Then the response status code should be 200
    And the JSON node "data.note" should be equal to "my note (edited)"
    And the JSON node "data.agent" should be equal to "{me}"
    And the JSON node "data.organization" should be equal to "{org1}"

  Scenario: I delete a note
    Given only the following "OrganizationNote" records exist:
      | #  | organization | agent | note    |
      | n1 | {org1}       | {me}  | my note |
    When I send a DELETE request to "/api/v2/organizations/{org1}/notes/{n1}"
    Then the response status code should be 200
