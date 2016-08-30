@new
Feature: /tickets/{id}/followers and /tickets/{id}/cc endpoints
  To CRUD DeskPRO ticket participants
  As an API user
  I want an API endpoint

  Background:
    Given no Person records exist
    And no Ticket records exist
    And no EmailAccount records exist
    And I'm authenticated as admin
    And agent and user exist
    And only the following Organization records exist:
      | #  | Name           |
      | o1 | Organization 1 |
      | o2 | Organization 2 |
    And the "user" record organization prop is equal to "{o1}"
    And the "admin" record organization prop is equal to "{o1}"
    And the "agent" record organization prop is equal to "{o2}"
    And only the following Ticket records exist:
      | #      | Subject           | Agent   |
      | ticket | First Demo Ticket | {admin} |

  Scenario: I retrieve lists of participants w/o sideloading
    Given I create a Ticket and reference it as ticket
    When I send a GET request to "/api/v2/tickets/{ticket}/cc"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements
    And the JSON node "linked" should have 0 elements

  Scenario: I try to add a ticket cc with empty request
    When I send a POST request to "/api/v2/tickets/{ticket}/cc"
    Then the response status code should be 400
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.person.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I add an agent as ticket cc
    When I send a POST request to "/api/v2/tickets/{ticket}/cc" with body:
    """
{
  "person": "agent@deskpro.dev"
}
    """
    Then the response status code should be 201

  Scenario: I add account email as cc
    Given I've just created a new person with name "Email account" and primary email "dev@deskprodev.com"
    Given I've just created a new email account "dev@deskprodev.com"
    When I send a POST request to "/api/v2/tickets/{ticket}/cc" with body:
    """
{
  "person": "dev@deskprodev.com"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "system_email"
    And the JSON node "errors.fields.person.errors[0].message" should contain "dev@deskprodev.com"
    And the JSON node "errors.fields.person.errors[0].message" should contain "is already being used as email account."

  Scenario: I add a ticket cc
    When I send a POST request to "/api/v2/tickets/{ticket}/cc" with body:
    """
{
  "person": "user@deskpro.dev"
}
    """
    Then the response status code should be 201
    And the JSON node "data.primary_email" should be equal to "user@deskpro.dev"

    When I send a GET request to "/api/v2/tickets/{ticket}/cc"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].primary_email" should be equal to "user@deskpro.dev"

    When I send a GET request to "/api/v2/tickets/{ticket}/cc?include=organization"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].primary_email" should be equal to "user@deskpro.dev"
    And the JSON node "linked.organization.{o1}" should exist
    And the JSON node "linked.organization.{o1}.id" should be equal to "{o1}"
    And the JSON node "linked.organization.{o1}.name" should be equal to "Organization 1"

  Scenario: I add followers
    When I send a POST request to "/api/v2/tickets/{ticket}/cc" with body:
    """
{
  "person": "agent@deskpro.dev"
}
    """
    Then the response status code should be 201

    When I send a POST request to "/api/v2/tickets/{ticket}/cc" with body:
    """
{
  "person": "admin@deskpro.dev"
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/{ticket}/cc"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].primary_email" should be equal to "admin@deskpro.dev"
    And the JSON node "data[1].primary_email" should be equal to "agent@deskpro.dev"

    When I send a GET request to "/api/v2/tickets/{ticket}/cc/{agent}?include=organization"
    Then the response status code should be 200
    And the JSON node "data.primary_email" should be equal to "agent@deskpro.dev"
    And the JSON node "linked.organization.{o2}" should exist
    And the JSON node "linked.organization.{o2}.name" should be equal to "Organization 2"

    When I send a GET request to "/api/v2/tickets/{ticket}/cc/{agent}"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{agent}"
    And the JSON node "data.primary_email" should be equal to "agent@deskpro.dev"
    And the JSON node "linked" should have 0 elements

    When I send a DELETE request to "/api/v2/tickets/{ticket}/cc/{agent}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tickets/{ticket}/cc/{agent}"
    Then the response status code should be 404

    When I send a GET request to "/api/v2/tickets/{ticket}/cc"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].primary_email" should be equal to "admin@deskpro.dev"
