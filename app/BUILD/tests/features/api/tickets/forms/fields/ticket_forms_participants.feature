@new
Feature: /ticket_forms
  I want to check participant fields

  Background:
    Given I'm authenticated as admin
    And "agent_1@deskpro.dev" agent exists
    And "agent_2@deskpro.dev" agent exists
    And "agent_3@deskpro.dev" agent exists
    And "user_1@deskpro.dev" user exists
    And "user_2@deskpro.dev" user exists
    And "user_3@deskpro.dev" user exists
    And the only default ticket layout exists with fields:
      | agent_layout |
      | cc           |
      | followers    |
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |

  Scenario: I add participants by email
    # TODO investigate what caused the increase of queries for this test
    Given I set max_queries=94, max_rows=1000
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "cc": ["agent_1@deskpro.dev", "agent_2@deskpro.dev", "user_1@deskpro.dev", "user_2@deskpro.dev"]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200

    And the JSON node "data.cc" should have 4 elements
    And the JSON node "data.cc[0]" should be equal to "~agent_1@deskpro.dev~"
    And the JSON node "data.cc[1]" should be equal to "~agent_2@deskpro.dev~"
    And the JSON node "data.cc[2]" should be equal to "~user_1@deskpro.dev~"
    And the JSON node "data.cc[3]" should be equal to "~user_2@deskpro.dev~"

  Scenario: I add participants by id
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "cc": [~agent_1@deskpro.dev~, ~agent_2@deskpro.dev~, ~user_1@deskpro.dev~, ~user_2@deskpro.dev~]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200

    And the JSON node "data.cc" should have 4 elements
    And the JSON node "data.cc[0]" should be equal to "~agent_1@deskpro.dev~"
    And the JSON node "data.cc[1]" should be equal to "~agent_2@deskpro.dev~"
    And the JSON node "data.cc[2]" should be equal to "~user_1@deskpro.dev~"
    And the JSON node "data.cc[3]" should be equal to "~user_2@deskpro.dev~"

  Scenario: I modify participants
    Given only the following TicketParticipant records exist:
      | Ticket | Person                |
      | {t1}   | ~agent_1@deskpro.dev~ |
      | {t1}   | ~agent_2@deskpro.dev~ |
      | {t1}   | ~user_1@deskpro.dev~  |
      | {t1}   | ~user_2@deskpro.dev~  |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "cc": [~agent_1@deskpro.dev~, ~agent_3@deskpro.dev~, ~user_1@deskpro.dev~]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200

    And the JSON node "data.cc" should have 3 elements
    And the JSON node "data.cc[0]" should be equal to "~agent_1@deskpro.dev~"
    And the JSON node "data.cc[1]" should be equal to "~agent_3@deskpro.dev~"
    And the JSON node "data.cc[2]" should be equal to "~user_1@deskpro.dev~"

  Scenario: I clear cc
    Given only the following TicketParticipant records exist:
      | Ticket | Person                |
      | {t1}   | ~agent_1@deskpro.dev~ |
      | {t1}   | ~agent_2@deskpro.dev~ |
      | {t1}   | ~user_1@deskpro.dev~  |
      | {t1}   | ~user_2@deskpro.dev~  |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "cc": []
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.cc" should have 0 elements

  Scenario: I add non existing email as CC
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "cc": ["new-user@deskpro.dev"]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.cc" should have 1 element
