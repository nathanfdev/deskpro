@tickets
Feature: Ticket link endpoint
  As a developer
  I want to check link/unlink tickets and fetch linked tickets list

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I link two tickets
    Given I reset ticket with id=1 logs
    When I send a "POST" request to "/api/v2/tickets/1/links" with body:
    """
{
  "parent": false,
  "link_ticket": 2
}
    """
    Then the response status code should be 204
    And the response should be empty
    And the header "Location" should be equal to "/api/v2/tickets/1/links"
    And ticket with id=2 has "parent_ticket" log

    When I send a GET request to "/api/v2/tickets/1/links"
    Then the JSON node "data.children[0].id" should be equal to 2
    Then the JSON node "data.count" should be equal to 1

  Scenario Outline: I'm trying to link ticket to itself
    When I send a "POST" request to "/api/v2/tickets/1/links" with body:
    """
{
  "parent": <parent>,
  "link_ticket": 1
}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors.fields.link_ticket.errors[0].code" should be equal to "link_itself"
    And the JSON node "errors.fields.link_ticket.errors[0].message" should be equal to "The object should not link itself."

    Examples:
      | parent |
      | true   |
      | false  |

  Scenario: I'm getting linked tickets list with sideloading
    When I send a "GET" request to "/api/v2/tickets/1/links?include=person,agent_team,organization"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.parent" should exist
    And the JSON node "data.siblings" should exist
    And the JSON node "data.children" should exist
    And the JSON node "data.children[0].id" should exist
    And the JSON node "data.children[0].id" should be equal to 2
    And the JSON node "linked.organization.1.id" should be equal to 1
    And the JSON node "linked.organization.1.name" should be equal to "Organization 1"
    And the JSON node "linked.organization.2.id" should be equal to 2
    And the JSON node "linked.organization.2.name" should be equal to "Organization 2"
    And the JSON node "linked.agent_team.2.id" should be equal to 2
    And the JSON node "linked.agent_team.2.name" should be equal to "Support Managers"
    And the JSON node "linked.person.1.id" should be equal to 1
    And the JSON node "linked.person.1.primary_email" should be equal to "admin@deskpro.dev"
    And the JSON node "linked.person.3.id" should be equal to 3
    And the JSON node "linked.person.3.primary_email" should be equal to "user@deskpro.dev"

  Scenario: I'm getting linked tickets list w/o sideloading
    When I send a "GET" request to "/api/v2/tickets/1/links"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.parent" should exist
    And the JSON node "data.siblings" should exist
    And the JSON node "data.children" should exist
    And the JSON node "data.children[0].id" should exist
    And the JSON node "data.children[0].id" should be equal to 2
    And the JSON node "linked" should have 0 elements

  Scenario: I link another two tickets
    Given I reset ticket with id=1 logs
    When I send a "POST" request to "/api/v2/tickets/1/links" with body:
    """
{
  "parent": true,
  "link_ticket": 3
}
    """
    Then the response status code should be 204
    And the response should be empty
    And the header "Location" should be equal to "/api/v2/tickets/1/links"
    When I send a "GET" request to "/api/v2/tickets/1/links"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.parent" should exist
    And the JSON node "data.siblings" should exist
    And the JSON node "data.children" should exist
    And the JSON node "data.parent.id" should exist
    And the JSON node "data.parent.id" should be equal to 3
    And ticket with id=1 has "parent_ticket" log

  Scenario: I link ticket to made it sibling to 1
    When I send a "POST" request to "/api/v2/tickets/4/links" with body:
    """
{
  "parent": true,
  "link_ticket": 3
}
    """
    Then the response status code should be 204
    And the response should be empty
    And the header "Location" should be equal to "/api/v2/tickets/4/links"
    When I send a "GET" request to "/api/v2/tickets/1/links"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.parent" should exist
    And the JSON node "data.siblings" should exist
    And the JSON node "data.children" should exist
    And the JSON node "data.siblings[0].id" should exist
    And the JSON node "data.siblings[0].id" should be equal to 4

    When I send a "GET" request to "/api/v2/tickets/3/links"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.parent" should exist
    And the JSON node "data.siblings" should exist
    And the JSON node "data.children" should exist
    And the JSON node "data.children[0].id" should exist
    And the JSON node "data.children[0].id" should be equal to 1
    And the JSON node "data.children[1].id" should exist
    And the JSON node "data.children[1].id" should be equal to 4

  Scenario: I unlink parent ticket
    Given I reset ticket with id=1 logs
    When I send a "DELETE" request to "/api/v2/tickets/1/links" with body:
    """
{
  "link_type": "parent"
}
    """
    Then the response status code should be 204
    And ticket with id=1 has "parent_ticket" log

    When I send a "GET" request to "/api/v2/tickets/1/links"
    Then the response status code should be 200
    And the JSON node "data.parent" should be null

  Scenario: I unlink child ticket
    Given I reset ticket with id=1 logs
    And I reset ticket with id=2 logs
    When I send a "DELETE" request to "/api/v2/tickets/1/links" with body:
    """
{
  "link_type": "child",
  "link_ticket": 2
}
    """
    Then the response status code should be 204
    And ticket with id=2 has "parent_ticket" log

    When I send a "GET" request to "/api/v2/tickets/1/links"
    Then the response status code should be 200
    And the JSON node "data.children" should have 0 elements
