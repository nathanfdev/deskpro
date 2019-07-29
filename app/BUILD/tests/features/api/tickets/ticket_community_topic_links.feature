@new
Feature: /tickets/{id}/community_topic_links endpoint
  To CRUD DeskPRO ticket community topic links

  Background:
    Given I'm authenticated as admin
    And no TicketCommunityTopicLink records exist
    And no CommunityTopic records exist
    And only the following Ticket records exist:
      | #  | Subject  | Status         | Person  |
      | t1 | Ticket 1 | awaiting_agent | {admin} |
    And only the following "CommunityChannel" records exist:
      | #   | title   | slug    |
      | cc1 | Feature | feature |
    And only the following CommunityTopic records exist:
      | #   | Title   | channel |
      | ct1 | Topic 1 | {cc1}   |
    And I reset ticket logs
    And I grant the "{cc1}" community channel permission for usergroup everyone

  Scenario: I retrieve an empty ticket community topic links
    When I send a GET request to "/api/v2/tickets/{t1}/community_topic_links"
    Then the response status code should be 200
    And the JSON node "data" should have 0 element

    When I send a GET request to "/api/v2/tickets/{t1}/community_topic_links/0"
    Then the response status code should be 404

  Scenario: I fail form validation
    When I send a POST request to "/api/v2/tickets/{t1}/community_topic_links"
    Then the response status code should be 400
    And the JSON node "errors.fields.topic.errors[0].code" should be equal to "required"

  Scenario: I add ticket community topic link
    Given "user_1@deskpro.dev" user exists
    And "user_2@deskpro.dev" user exists
    And only the following TicketParticipant records exist:
      | Ticket | Person               |
      | {t1}   | ~user_1@deskpro.dev~ |
      | {t1}   | ~user_2@deskpro.dev~ |
    When I reset ticket logs
    And I send a POST request to "/api/v2/tickets/{t1}/community_topic_links" with body:
    """
{
  "topic": ~ct1~,
  "is_subscribe_ticket_owner": 1,
  "is_subscribe_ticket_participants": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.person" should be equal to "{admin}"
    And the JSON node "data.topic" should be equal to "{ct1}"
    And the JSON node "data.ticket" should be equal to "{t1}"
    And the "{t1}" ticket should have "community_topic_link_added" log
    And the "{t1}" ticket should have "action_starter" log with detail "event_performer" = "agent"
    And the "{ct1}" community topic should have subscribed persons "{admin},{~user_1@deskpro.dev~},{~user_2@deskpro.dev~}"

  Scenario: I retrieve ticket community topic links by ticket id and by ticket ref
    Given only the following TicketCommunityTopicLink records exist:
      | #   | Person  | Ticket | Topic |
      | ttf | {admin} | {t1}   | {ct1} |

    When I send a GET request to "/api/v2/tickets/{t1}/community_topic_links"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements
    And the JSON node "data[0].id" should be equal to "{ttf}"

    When I send a GET request to "/api/v2/tickets/ref:{t1:ref}/community_topic_links"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements
    And the JSON node "data[0].id" should be equal to "{ttf}"

  Scenario: I delete ticket community topic link
    Given only the following TicketCommunityTopicLink records exist:
      | #   | Person  | Ticket | Topic |
      | ttf | {admin} | {t1}   | {ct1} |

    When I reset ticket logs
    And I send a DELETE request to "/api/v2/tickets/{t1}/community_topic_links/{ttf}"
    Then the response status code should be 200
    And the "{t1}" ticket should have "community_topic_link_removed" log
    And the "{t1}" ticket should have "action_starter" log with detail "event_performer" = "agent"

    When I send a GET request to "/api/v2/tickets/{t1}/community_topic_links/{ttf}"
    Then the response status code should be 404