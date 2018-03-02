Feature: /tickets/{id}/feedback_links endpoint
  To CRUD DeskPRO ticket feedback links

  Background:
    Given I'm authenticated as admin
    And no TicketFeedbackLink records exist
    And no Feedback records exist
    And only the following Ticket records exist:
      | #  | Subject  | Status         | Person   |
      | t1 | Ticket 1 | awaiting_agent | {admin}  |
    And only the following "FeedbackCategory" records exist:
      | #   | title      | slug       |
      | fc1 | Feature    | feature    |
    And only the following Feedback records exist:
      | #  | Title      | category  |
      | f1 | Feedback 1 | {fc1}     |
    And I reset ticket logs
    And I grant the "{fc1}" feedback category permission for usergroup everyone

  Scenario: I retrieve an empty ticket feedback links
    When I send a GET request to "/api/v2/tickets/{t1}/feedback_links"
    Then the response status code should be 200
    And the JSON node "data" should have 0 element

    When I send a GET request to "/api/v2/tickets/{t1}/feedback_links/0"
    Then the response status code should be 404

  Scenario: I fail form validation
    When I send a POST request to "/api/v2/tickets/{t1}/feedback_links"
    Then the response status code should be 400
    And the JSON node "errors.fields.feedback.errors[0].code" should be equal to "required"

  Scenario: I add ticket feedback link
    Given "user_1@deskpro.dev" user exists
    And "user_2@deskpro.dev" user exists
    And only the following TicketParticipant records exist:
      | Ticket | Person                |
      | {t1}   | ~user_1@deskpro.dev~  |
      | {t1}   | ~user_2@deskpro.dev~  |
    When I send a POST request to "/api/v2/tickets/{t1}/feedback_links" with body:
    """
{
  "feedback": ~f1~,
  "is_subscribe_ticket_owner": 1,
  "is_subscribe_ticket_participants": 1
}
    """
    Then the response status code should be 201
    And the JSON node "data.person" should be equal to "{admin}"
    And the JSON node "data.feedback" should be equal to "{f1}"
    And the JSON node "data.ticket" should be equal to "{t1}"
    And the "{t1}" ticket should have "feedback_link_added" log
    And the "{f1}" feedback should have subscribed persons "{admin},{~user_1@deskpro.dev~},{~user_2@deskpro.dev~}"

  Scenario: I retrieve ticket feedback links by ticket id and by ticket ref
    Given only the following TicketFeedbackLink records exist:
      | #   | Person  | Ticket | Feedback |
      | ttf | {admin} | {t1}   | {f1}     |

    When I send a GET request to "/api/v2/tickets/{t1}/feedback_links"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements
    And the JSON node "data[0].id" should be equal to "{ttf}"

    When I send a GET request to "/api/v2/tickets/ref:{t1:ref}/feedback_links"
    Then the response status code should be 200
    And the JSON node "data" should have 1 elements
    And the JSON node "data[0].id" should be equal to "{ttf}"

  Scenario: I delete ticket feedback link
    Given only the following TicketFeedbackLink records exist:
      | #   | Person  | Ticket | Feedback |
      | ttf | {admin} | {t1}   | {f1}     |

    When I send a DELETE request to "/api/v2/tickets/{t1}/feedback_links/{ttf}"
    Then the response status code should be 200
    And the "{t1}" ticket should have "feedback_link_removed" log

    When I send a GET request to "/api/v2/tickets/{t1}/feedback_links/{ttf}"
    Then the response status code should be 404