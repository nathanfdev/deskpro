@new
Feature: Ticket logs
  To observe ticket history
  As a DeskPRO user
  I want all ticket actions to have corresponding logs

  Background:
    Given I'm authenticated as admin
    And I have a Department record referenced as demo_department
    And I create a Ticket with department equal to "{demo_department}" and reference it as demo_ticket
    And there are no CustomDefTicket records
    And agent and user exist

  Scenario: I delete a ticket follower and verify logs
    Given I add the following TicketParticipant records:
      | Ticket        | Person  |
      | {demo_ticket} | {agent} |
    And I reset the "{demo_ticket}" ticket logs
    When I send a DELETE request to "/api/v2/tickets/{demo_ticket}/cc/{agent}"
    And the "{demo_ticket}" ticket should have "changed_agent_participants" log

  Scenario: I create a ticket and check its' logs
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Modified 1",
  "department": ~demo_department~,
  "person":  ~user~,
  "agent": ~agent~
}
    """
    Then the "{lastCreatedId}" ticket should have the following logs:
      | type               |
      | action_starter     |
      | ticket_created     |
      | changed_subject    |
      | changed_department |
      | changed_person     |
      | changed_agent      |

  Scenario: I modify a ticket and check its' logs
    Given I reset the "{demo_ticket}" ticket logs
    When I send a PUT request to "/api/v2/tickets/{demo_ticket}" with body:
    """
{
  "subject": "Modified 3",
  "cc": [~agent~]
}
    """
    Then the "{demo_ticket}" ticket should have no the following logs:
      | type                      |
      | changed_department        |
      | changed_person            |
      | changed_agent             |
      | changed_user_participants |
    And the "{demo_ticket}" ticket should have the following logs:
      | type                       |
      | changed_subject            |
      | changed_agent_participants |

  Scenario: I add a ticket cc and verify logs
    And I reset the "{demo_ticket}" ticket logs
    When I send a POST request to "/api/v2/tickets/{demo_ticket}/cc" with body:
    """
{
  "person": "user@deskpro.dev"
}
    """
    Then the response status code should be 201
    And the "{demo_ticket}" ticket should have "changed_user_participants" log

  Scenario: I delete a ticket and check its' logs
    Given only the following TicketStatus records exist:
      | #   | StatusType     | SysId        | Title    |
      | ts1 | hidden         | deleted      | Deleted  |

    When I send a DELETE request to "/api/v2/tickets/{demo_ticket}"
    Then the "{demo_ticket}" ticket should have the following logs:
      | type                  |
      | changed_status        |
