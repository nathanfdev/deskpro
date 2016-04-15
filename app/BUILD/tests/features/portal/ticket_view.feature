Feature: Ticket View
  Viewing tickets

  Background: Fresh DB
    Given I install the fresh data set
    And the setting "core_tickets.use_ref" is set to 0
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And the organization "walmart" exists
    And "user" is an organization manager of "walmart"
    And the following tickets exist:
      | who | subject | status | organization |
      | user  | My Ticket   | awaiting_user | walmart |
      | user  | Is this normal?   | awaiting_agent | walmart |
      | user   | My Other Ticket   | awaiting_agent | walmart |
      | user   | Help Needed   |   awaiting_agent  |walmart |
      | user   | This is resolved   |   resolved  | walmart|
      | user   | Can you add this feature for me?   |   awaiting_user  |walmart|
      | agent   | An agent ticket 1  |   awaiting_user  | walmart |
      | agent   | An agent ticket 2   |   awaiting_user  | |
      | agent   | An agent ticket 3   |   awaiting_user  | |
      | agent   | An agent ticket 4   |   awaiting_agent  | |
      | agent   | An agent ticket 5   |   awaiting_agent  | walmart |
      | agent   | An agent ticket 6   |   resolved  | |
      | agent   | An agent ticket 7   |   resolved  | walmart |
      | agent   | An agent ticket 8   |   resolved  | walmart |
      | agent   | An agent ticket 9   |   resolved  | |

  @reinstall
  Scenario: Trying to view a ticket when not logged in should make me log in
    Given I go to "/tickets/1"
    Then I should be on "/login"
    And the response status code should be 200
    When I go to "/tickets/9"
    Then I should be on "/login"
    And the response status code should be 200

  @reinstall
  Scenario: Viewing my own ticket
    Given I login with user credentials
    When I go to "/tickets/1"
    Then the response status code should be 200
    And I should see "My Ticket"

  @reinstall
  Scenario: Trying to view my own ticket as an agent
    Given I login with agent credentials
    When I go to "/tickets/9"
    Then the response status code should be 200
    And I should see "An agent ticket 3"

  @reinstall
  Scenario: Trying to view someone else's ticket as an agent should give me a 403 if I am logged in
    Given I login with agent credentials
    When I go to "/tickets/1"
    Then the response status code should be 403

  @reinstall
  Scenario: Trying to view someone else's ticket should give me a 403 if I am logged in
    Given I login with user credentials
    When I go to "/tickets/9"
    Then the response status code should be 403

  @reinstall
  Scenario: Trying to view someone else's ticket as the organization manager of that ticket's org
    Given I login with user credentials
    When I go to "/tickets/7"
    Then the response status code should be 200
    And I should see "An agent ticket 1"

  @reinstall
  Scenario: If you are not logged in, you cannot view the "guest view" of a ticket. You must login first.
    When I go to the ticket view page for ticket ID "1"
    Then I should be on "/login"
    And the response status code should be 200

  @reinstall
  Scenario: Access a ticket view as a logged in user for a ticket you are not a part of, and replying to it makes you a participant
    Given I login with user credentials
    And "user" am not involved with ticket ID "9"
    When I go to the ticket view page for ticket ID "9"
    Then I should be on the ticket view page for ticket ID "9"
    And I should see "An agent ticket 3"
    And I should see the ticket reply form
    When I fill in "ticket_reply_ticket_message_message" with "This is my reply"
    And I press "Reply"
    Then I should be on "/tickets/9"
    And the response status code should be 200
    And I should see a success flash message with the phrase "portal.flashes.ticket_replied"
    And "user" should be a participant on ticket ID "9"
