Feature: Ticket Participants
  A user should see tickets they participant in the lists, but an agent shouldn't.
  If you are a participant you should have access.

  Background: Fresh DB
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the organization "walmart" exists
    And the default brand is using the standard theme
    And the following tickets exist:
      | who | subject | status | participant | organization |
      | user  | My Ticket   | awaiting_user | agent | walmart |
      | user  | Is this normal?   | awaiting_agent | agent |  |
      | agent   | An agent ticket 1   |   awaiting_user  | user |  |
      | agent   | An agent ticket 2   |   awaiting_user  | user | walmart |
      | agent   | An agent ticket 3   |   awaiting_agent  | user |  |
      | agent   | An agent ticket 4   |   awaiting_agent  | user | walmart |

  @reinstall
  Scenario: An agent does not see the participant tickets in the lists
    Given I login with agent credentials
    When I go to "/tickets"
    Then I should see "2" tickets "awaiting_user"
    And I should see "2" tickets "awaiting_agent"
    And I should see a header ticket count of "4"

  @reinstall
  Scenario: An organization manager sees the participant tickets in the lists, but only the ones that are not a part of the organization
    Given user is an organization manager of "walmart"
    And I login with user credentials
    When I go to "/tickets"
    Then I should see "2" tickets "awaiting_user"
    And I should see "2" tickets "awaiting_agent"
    And I should see a header ticket count of "4"
    And I should see a header organization ticket count of "4"

  @reinstall
  Scenario: A user that is not an organization manager sees all participating tickets (unlike the org manager above)
    Given I login with user credentials
    When I go to "/tickets"
    Then I should see "3" tickets "awaiting_user"
    And I should see "3" tickets "awaiting_agent"
    And I should see a header ticket count of "6"

  @reinstall
  Scenario: An agent cant view a ticket they participate in through portal
    Given I login with agent credentials
    When I go to "/tickets/1"
    Then the response status code should be 403


  @reinstall
  Scenario: A user can view a ticket they participate in
    Given I login with user credentials
    When I go to "/tickets/3"
    Then the response status code should be 200
    And I should see "An agent ticket 1"
