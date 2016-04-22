@basic
Feature: Ticket List
  Viewing a ticket list

  Background: Fresh DB
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  @reinstall
  Scenario: Viewing the ticket list when unautneticated sends you to the login page
    Given the organization "walmart" exists
    And "user" is an organization manager of "walmart"
    And the following tickets exist:
      | who   | subject                          | status         | organization |
      | user  | My Ticket                        | awaiting_user  | walmart      |
      | user  | Is this normal?                  | awaiting_agent | walmart      |
      | user  | My Other Ticket                  | awaiting_agent | walmart      |
      | user  | Help Needed                      | awaiting_agent | walmart      |
      | user  | This is resolved                 | resolved       | walmart      |
      | user  | Can you add this feature for me? | awaiting_user  | walmart      |
      | agent | An agent ticket 1                | awaiting_user  | walmart      |
      | agent | An agent ticket 2                | awaiting_user  |              |
      | agent | An agent ticket 3                | awaiting_user  |              |
      | agent | An agent ticket 4                | awaiting_agent |              |
      | agent | An agent ticket 5                | awaiting_agent | walmart      |
      | agent | An agent ticket 6                | resolved       |              |
      | agent | An agent ticket 7                | resolved       | walmart      |
      | agent | An agent ticket 8                | resolved       | walmart      |
      | agent | An agent ticket 9                | resolved       |              |
    And I go to "/tickets"
    Then I should be on "/login"
    And the response status code should be 200

  Scenario: Viewing my tickets list
    Given I login with user credentials
    When I go to "/tickets"
    Then I should see "2" tickets "awaiting_user"
    And I should see "3" tickets "awaiting_agent"
    And I should see a header ticket count of "6"
    And I should see a header organization ticket count of "10"

  Scenario: Viewing the resolved ticket list
    Given I login with user credentials
    When I go to "/tickets"
    And I follow "Resolved"
    Then I should be on "/tickets/resolved"
    And the response status code should be 200
    And I should see "1" tickets "resolved"
    And I should see a header ticket count of "6"
    And I should see a header organization ticket count of "10"

  Scenario: Viewing my organization tickets
    Given I login with user credentials
    When I go to "/tickets/organization"
    Then I should see "3" tickets "awaiting_user"
    And I should see "4" tickets "awaiting_agent"
    And I should see a header organization ticket count of "10"

  Scenario: Viewing an agent's ticket list
    Given I login with agent credentials
    When I go to "/tickets"
    Then I should see "3" tickets "awaiting_user"
    And I should see "2" tickets "awaiting_agent"
    And I should see a header ticket count of "9"
    When I follow "Resolved"
    Then I should be on "/tickets/resolved"
    And the response status code should be 200
    And I should see "4" tickets "resolved"
    And I should see a header ticket count of "9"
