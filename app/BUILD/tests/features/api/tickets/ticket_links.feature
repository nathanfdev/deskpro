@new
Feature: Ticket link endpoint
  As an API user
  I want to check link/unlink tickets and fetch linked tickets list

  Background:
    Given I'm authenticated as admin
    And agent and user exist
    And I have an AgentTeam record with name equal to "Demo team" which is referenced as agent_team
    And I create an Organization with name equal to "Demo organization" and reference it as organization
    And only the following Ticket records exist:
      | #        | Subject      | Organization   | Agent team   | Agent   | Person |
      | ticket_1 | Ticket One   |                |              | {agent} | {user} |
      | ticket_2 | Ticket Two   | {organization} | {agent_team} | {admin} | {user} |
      | ticket_3 | Ticket Three |                |              |         | {user} |
      | ticket_4 | Ticket Four  |                |              |         | {user} |
      | ticket_5 | Ticket Five  |                |              |         | {user} |
      | ticket_6 | Ticket Six   |                |              |         | {user} |

  Scenario: I link two tickets (add children ticket)
    When I send a "POST" request to "/api/v2/tickets/{ticket_1}/links" with body:
    """
{
  "parent": false,
  "link_ticket": ~ticket_2~
}
    """
    Then the response status code should be 204
    And the response should be empty
    And the header "Location" should be equal to "/api/v2/tickets/{ticket_1}/links"

    When I send a GET request to "/api/v2/tickets/{ticket_1}/links"
    Then the JSON node "data.children[0].id" should be equal to "{ticket_2}"
    Then the JSON node "data.count" should be equal to 1

  Scenario Outline: I'm trying to link ticket to itself
    When I send a "POST" request to "/api/v2/tickets/{ticket_1}/links" with body:
    """
{
  "parent": <parent>,
  "link_ticket": ~ticket_1~
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
    Given I send a "POST" request to "/api/v2/tickets/{ticket_1}/links" with body:
    """
{
  "parent": false,
  "link_ticket": ~ticket_2~
}
    """
    When I send a "GET" request to "/api/v2/tickets/{ticket_1}/links?include=person,agent_team,organization"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.parent" should exist
    And the JSON node "data.siblings" should exist
    And the JSON node "data.children" should exist
    And the JSON node "data.children[0].id" should be equal to "{ticket_2}"
    And the JSON node "linked.organization.{organization}.name" should be equal to "Demo organization"
    And the JSON node "linked.agent_team.{agent_team}.name" should be equal to "Demo team"
    And the JSON node "linked.person.{admin}.primary_email" should be equal to "admin@deskpro.dev"

  Scenario: I'm getting linked tickets list w/o sideloading
    Given I send a "POST" request to "/api/v2/tickets/{ticket_1}/links" with body:
    """
{
  "parent": false,
  "link_ticket": ~ticket_2~
}
    """
    When I send a "GET" request to "/api/v2/tickets/{ticket_1}/links"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.parent" should exist
    And the JSON node "data.siblings" should exist
    And the JSON node "data.children" should exist
    And the JSON node "data.children[0].id" should be equal to "{ticket_2}"
    And the JSON node "linked" should have 0 elements

  Scenario: I link another two tickets
    When I send a "POST" request to "/api/v2/tickets/{ticket_1}/links" with body:
    """
{
  "parent": true,
  "link_ticket": ~ticket_3~
}
    """
    Then the response status code should be 204
    And the response should be empty
    And the header "Location" should be equal to "/api/v2/tickets/{ticket_1}/links"
    When I send a "GET" request to "/api/v2/tickets/{ticket_1}/links"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.parent" should exist
    And the JSON node "data.siblings" should exist
    And the JSON node "data.children" should exist
    And the JSON node "data.parent.id" should exist

  Scenario: I link two tickets to the same parent, verify they became siblings and then unlink them
    When I send a "POST" request to "/api/v2/tickets/{ticket_4}/links" with body:
    """
{
  "parent": true,
  "link_ticket": ~ticket_6~
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a "POST" request to "/api/v2/tickets/{ticket_5}/links" with body:
    """
{
  "parent": true,
  "link_ticket": ~ticket_6~
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a "GET" request to "/api/v2/tickets/{ticket_4}/links"
    Then the JSON node "data.parent.id" should be equal to "{ticket_6}"
    And the JSON node "data.siblings[0].id" should be equal to "{ticket_5}"

    When I send a "GET" request to "/api/v2/tickets/{ticket_5}/links"
    Then the JSON node "data.parent.id" should be equal to "{ticket_6}"
    And the JSON node "data.siblings[0].id" should be equal to "{ticket_4}"

    When I send a "GET" request to "/api/v2/tickets/{ticket_6}/links"
    Then the response status code should be 200
    And the JSON node "data.parent" should be null
    And the JSON node "data.children" should have 2 elements
    And the JSON node "data.children[0].id" should be equal to "{ticket_4}"
    And the JSON node "data.children[1].id" should be equal to "{ticket_5}"

    When I send a "DELETE" request to "/api/v2/tickets/{ticket_5}/links" with body:
    """
{
  "link_type": "parent"
}
    """
    Then the response status code should be 204
    When I send a "GET" request to "/api/v2/tickets/{ticket_5}/links"
    Then the response status code should be 200
    And the JSON node "data.parent" should be null

    When I send a "GET" request to "/api/v2/tickets/{ticket_6}/links"
    Then the response status code should be 200
    And the JSON node "data.children" should have 1 element
    And the JSON node "data.children[0].id" should be equal to "{ticket_4}"

    When I send a "DELETE" request to "/api/v2/tickets/{ticket_6}/links" with body:
    """
{
  "link_type": "child",
  "link_ticket": ~ticket_4~
}
    """
    Then the response status code should be 204
    When I send a "GET" request to "/api/v2/tickets/{ticket_6}/links"
    Then the response status code should be 200
    And the JSON node "data.children" should have 0 elements

  Scenario Outline: I try to link non-existing ticket
    When I send a "POST" request to "/api/v2/tickets/{ticket_4}/links" with body:
    """
{
  "parent": <parent>,
  "link_ticket": 404
}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors.fields.link_ticket.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.link_ticket.errors[0].message" should be equal to "One or more of the given values is invalid."

    Examples:
      | parent |
      | true   |
      | false  |

  Scenario Outline: I try to unlink non-existing ticket
    When I send a "DELETE" request to "/api/v2/tickets/{ticket_4}/links" with body:
    """
{
  "link_type": <link_type>,
  "link_ticket": 404
}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors.fields.link_ticket.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.link_ticket.errors[0].message" should be equal to "One or more of the given values is invalid."

    Examples:
      | link_type   |
      | "child"     |
      | "sibling"   |
