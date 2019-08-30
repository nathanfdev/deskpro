@new
Feature: /approval_types endpoint
  To CRUD DeskPRO approval types
  As an API user
  I want an API endpoint

  Background:
    Given no ApprovalTemplate records exist
    And no TicketApproval records exist
    And no ApprovalResponse records exist
    And no ApprovalType records exist
    Given only the following ApprovalType records exist:
      | #   | name            | description    | isDeleted |
      | at1 | Approval Type 1 | Description 1  | 0         |
      | at2 | Approval Type 2 | Description 2  | 1         |
      | at3 | Approval Type 3 | Description 3  | 0         |

  Scenario: I try to POST an approval type without authentication
    When I send a POST request to "/api/v2/approval_types"
    Then the response status code should be 401

  Scenario: I try to POST an approval type as an agent
    Given I'm authenticated as "agent"
    When I send a POST request to "/api/v2/approval_types"
    Then the response status code should be 403

  Scenario: I try to POST an approval type as a user
    Given I'm authenticated as "user"
    When I send a POST request to "/api/v2/approval_types"
    Then the response status code should be 403

  Scenario: I try to POST an approval type without required field title as admin
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_types"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.name.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.name.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I POST a valid approval type without optional values as admin
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_types" with body:
      """
{
  "name": "New Approval Type 2"
}
      """
    Then the response status code should be 201
    Then the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    And the JSON node "data.name" should be equal to "New Approval Type 2"
    And the JSON node "data.description" should be equal to ""
    And the JSON node "data.is_deleted" should be false

  Scenario: I POST a valid approval type with optional values as admin
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_types" with body:
      """
{
  "name": "Approval Type 3",
  "description": "Description 3",
  "is_deleted": true
}
      """
    Then the response status code should be 201
    Then the response should be in JSON
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    And the JSON node "data.name" should be equal to "Approval Type 3"
    And the JSON node "data.description" should be equal to "Description 3"
    And the JSON node "data.is_deleted" should be true

  Scenario: I try to GET an approval type without authentication
    When I send a GET request to "/api/v2/approval_types/{at1}"
    Then the response status code should be 401

  Scenario: I GET an approval type which exists as user
    Given I'm authenticated as "user"
    When I send a GET request to "/api/v2/approval_types/{at1}"
    Then the response status code should be 403

  Scenario: I GET an approval type which does not exist
    Given I'm authenticated as "admin"
    When I send a GET request to "/api/v2/approval_types/99999"
    Then the response status code should be 404
    Then the response should be in JSON
    And the JSON node "code" should be equal to "#99999 Not Found"
    And the JSON node "message" should be equal to "#99999 Not Found"

  Scenario: I GET an approval type which exists as admin
    Given I'm authenticated as "admin"
    When I send a GET request to "/api/v2/approval_types/{at1}"
    Then the response status code should be 200
    Then the response should be in JSON
    And the JSON node "data.id" should be equal to "{at1}"
    And the JSON node "data.name" should be equal to "Approval Type 1"
    And the JSON node "data.description" should be equal to "Description 1"
    And the JSON node "data.is_deleted" should be false

  Scenario: I GET an approval type which exists as agent
    Given I'm authenticated as "agent"
    When I send a GET request to "/api/v2/approval_types/{at1}"
    Then the response status code should be 200
    Then the response should be in JSON
    And the JSON node "data.id" should be equal to "{at1}"
    And the JSON node "data.name" should be equal to "Approval Type 1"
    And the JSON node "data.description" should be equal to "Description 1"
    And the JSON node "data.is_deleted" should be false

  Scenario: I try to GET a list of approval types without authentication
    When I send a GET request to "/api/v2/approval_types"
    Then the response status code should be 401

  Scenario: I GET a list of approval types and I do not request to return deleted approval types
    Given I'm authenticated as "admin"
    When I send a GET request to "/api/v2/approval_types"
    Then the response status code should be 200
    And the JSON node "meta.pagination.total" should be equal to "2"
    And the JSON node "data[0].name" should be equal to "Approval Type 1"
    And the JSON node "data[1].name" should be equal to "Approval Type 3"

  Scenario: I GET a list of approval types and I request to return deleted approval types
    Given I'm authenticated as "admin"
    When I send a GET request to "/api/v2/approval_types?is_deleted=true"
    Then the response status code should be 200
    And the JSON node "meta.pagination.total" should be equal to "1"
    And the JSON node "data[0].name" should be equal to "Approval Type 2"

  Scenario: I try to POST an approval type without authentication
    When I send a PUT request to "/api/v2/approval_types/{at1}"
    Then the response status code should be 401

  Scenario: I PUT an existing approval type as user
    Given I'm authenticated as "user"
    When I send a PUT request to "/api/v2/approval_types/{at1}"
    Then the response status code should be 403

  Scenario: I PUT an existing approval type as agent
    Given I'm authenticated as "agent"
    When I send a PUT request to "/api/v2/approval_types/{at1}"
    Then the response status code should be 403

  Scenario: I PUT a non-existing approval type as admin
    Given I'm authenticated as "admin"
    When I send a PUT request to "/api/v2/approval_types/99999"
    Then the response status code should be 404

  Scenario: I PUT an existing approval type as admin
    Given I'm authenticated as "admin"
    When I send a PUT request to "/api/v2/approval_types/{at1}" with body:
      """
{
  "name": "Approval Type Delta",
  "description": "Description Delta"
}
      """
    Then the response status code should be 204
    And I send a GET request to "/api/v2/approval_types/{at1}"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "Approval Type Delta"
    And the JSON node "data.description" should be equal to "Description Delta"
    And the JSON node "data.is_deleted" should be false

  Scenario: I try to DELETE an approval type without authentication
    When I send a DELETE request to "/api/v2/approval_types/{at1}"
    Then the response status code should be 401

  Scenario: I DELETE an existing approval type as user
    Given I'm authenticated as "user"
    When I send a DELETE request to "/api/v2/approval_types/{at1}"
    Then the response status code should be 403

  Scenario: I DELETE an existing approval type as agent
    Given I'm authenticated as "agent"
    When I send a DELETE request to "/api/v2/approval_types/{at1}"
    Then the response status code should be 403

  Scenario: I DELETE an approval type as admin
    Given I'm authenticated as "admin"
    When I send a DELETE request to "/api/v2/approval_types/{at1}"
    Then the response status code should be 200
    When I send a GET request to "/api/v2/approval_types"
    Then the response status code should be 200
    And the JSON node "meta.pagination.total" should be equal to "1"

  Scenario: I count approval types as a admin
    Given I'm authenticated as "admin"
    When I send a GET request to "/api/v2/approval_types/counts"
    Then the response status code should be 200
    And the JSON node "data.count" should be equal to "2"
