@tickets
Feature: /tickets/{id}/followers and /tickets/{id}/cc endpoints
  To CRUD DeskPRO ticket participants
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve lists of participants w/o sideloading
    When I send a GET request to "/api/v2/tickets/1/cc"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements
    And the JSON node "linked" should have 0 elements

    When I send a GET request to "/api/v2/tickets/1/followers"
    Then the response status code should be 200
    And the JSON node "data" should have 0 element
    And the JSON node "linked" should have 0 elements

  Scenario: I try to add a ticket cc with empty request
    When I send a POST request to "/api/v2/tickets/1/cc"
    Then the response status code should be 400
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.person.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I add an agent as ticket cc
    When I send a POST request to "/api/v2/tickets/1/cc" with body:
    """
{
  "person": "agent@deskpro.dev"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.person.errors[0].code" should be equal to "person_not_user"
    And the JSON node "errors.fields.person.errors[0].message" should contain "Person with identifier"
    And the JSON node "errors.fields.person.errors[0].message" should contain "agent@deskpro.dev"

  Scenario: I add a ticket cc
    Given I reset ticket with id=1 logs
    When I send a POST request to "/api/v2/tickets/1/cc" with body:
    """
{
  "person": "user@deskpro.dev"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.primary_email" should be equal to "user@deskpro.dev"
    And ticket with id=1 has "changed_user_participants" log

  Scenario: I retrieve lists of participants w/o sideloading
    When I send a GET request to "/api/v2/tickets/1/cc"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 3
    And the JSON node "data[0].primary_email" should be equal to "user@deskpro.dev"

  Scenario: I retrieve lists of participants with sideloading
    When I send a GET request to "/api/v2/tickets/1/cc?include=organization"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 3
    And the JSON node "data[0].primary_email" should be equal to "user@deskpro.dev"
    And the JSON node "linked.organization.1" should exist
    And the JSON node "linked.organization.1.id" should be equal to 1
    And the JSON node "linked.organization.1.name" should be equal to "Organization 1"

  Scenario: I add followers
    When I send a POST request to "/api/v2/tickets/1/followers" with body:
    """
{
  "person": "agent@deskpro.dev"
}
    """
    Then the response status code should be 201

    When I send a POST request to "/api/v2/tickets/1/followers" with body:
    """
{
  "person": "admin@deskpro.dev"
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/tickets/1/followers"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].primary_email" should be equal to "admin@deskpro.dev"
    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].primary_email" should be equal to "agent@deskpro.dev"

  Scenario: I get follower with sideloading
    When I send a GET request to "/api/v2/tickets/1/followers/2?include=organization"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.primary_email" should be equal to "agent@deskpro.dev"
    And the JSON node "linked.organization.2" should exist
    And the JSON node "linked.organization.2.id" should be equal to 2
    And the JSON node "linked.organization.2.name" should be equal to "Organization 2"

  Scenario: I get follower w/o sideloading
    When I send a GET request to "/api/v2/tickets/1/followers/2"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.primary_email" should be equal to "agent@deskpro.dev"
    And the JSON node "linked" should have 0 elements

  Scenario: I delete follower
    Given I reset ticket with id=1 logs
    When I send a DELETE request to "/api/v2/tickets/1/followers/2"
    Then the response status code should be 200
    And ticket with id=1 has "changed_agent_participants" log

    When I send a GET request to "/api/v2/tickets/1/followers/2"
    Then the response status code should be 404

    When I send a GET request to "/api/v2/tickets/1/followers"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].primary_email" should be equal to "admin@deskpro.dev"
