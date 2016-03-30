@tickets
Feature: Ticket link endpoint
  As a developer
  I want to check link/unlink tickets and fetch linked tickets list

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I link two tickets
    When I send a "POST" request to "/api/v2/tickets/1/link" with body:
    """
{
  "parent": false,
  "link_ticket_id": 2
}
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Location" should be equal to "/api/v2/tickets/1/link"

  Scenario: I'm trying to link ticket to itself
    When I send a "POST" request to "/api/v2/tickets/1/link" with body:
    """
{
  "parent": false,
  "link_ticket_id": 1
}
    """
    Then the response status code should be 400
    And the response should be in JSON

  Scenario: I'm getting linked tickets list with sideloading
    When I send a "GET" request to "/api/v2/tickets/1/link?include=person,agent_team,organization"
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
    When I send a "GET" request to "/api/v2/tickets/1/link"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.parent" should exist
    And the JSON node "data.siblings" should exist
    And the JSON node "data.children" should exist
    And the JSON node "data.children[0].id" should exist
    And the JSON node "data.children[0].id" should be equal to 2
    And the JSON node "linked" should have 0 elements

  Scenario: I link another two tickets
    When I send a "POST" request to "/api/v2/tickets/1/link" with body:
    """
{
  "parent": true,
  "link_ticket_id": 3
}
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Location" should be equal to "/api/v2/tickets/1/link"
    When I send a "GET" request to "/api/v2/tickets/1/link"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.parent" should exist
    And the JSON node "data.siblings" should exist
    And the JSON node "data.children" should exist
    And the JSON node "data.parent.id" should exist
    And the JSON node "data.parent.id" should be equal to 3


  Scenario: I link ticket to made it sibling to 1
    When I send a "POST" request to "/api/v2/tickets/4/link" with body:
    """
{
  "parent": true,
  "link_ticket_id": 3
}
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Location" should be equal to "/api/v2/tickets/4/link"
    When I send a "GET" request to "/api/v2/tickets/1/link"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.parent" should exist
    And the JSON node "data.siblings" should exist
    And the JSON node "data.children" should exist
    And the JSON node "data.siblings[0].id" should exist
    And the JSON node "data.siblings[0].id" should be equal to 4

  Scenario: I check if ticket #3 has children
    When I send a "GET" request to "/api/v2/tickets/3/link"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.parent" should exist
    And the JSON node "data.siblings" should exist
    And the JSON node "data.children" should exist
    And the JSON node "data.children[0].id" should exist
    And the JSON node "data.children[0].id" should be equal to 1
    And the JSON node "data.children[1].id" should exist
    And the JSON node "data.children[1].id" should be equal to 4