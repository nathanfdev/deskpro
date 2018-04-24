@new
Feature: I check count versioning

  Background:
    Given no Person records exist
    And I'm authenticated as agent
    And "user@deskpro.dev" user exists
    And the following "Department" records exist:
      | #  | title           | is_chat_enabled |
      | d1 | ChatDepartment1 | 1               |
    And only the following Chat records exist:
      | #  | subject | date_created | agent   | person | department |
      | c1 | Chat1   | 2000-01-02   | {agent} | {user} | {d1}       |

  Scenario: I check 20160101 int identity
    When I send a GET request to "/api/v2/20160101/user_chats/counts?group_by=agent"
    Then the JSON node "data.nested[0].id" should be equal to "{agent}"
    And the JSON node "data.nested[0].value" should not exist
    And the JSON node "data.nested[0].type" should be equal to "agent"
    And the JSON node "data.nested[0].title" should be equal to "Agent Agent"
    And the JSON node "data.nested[0].count" should be equal to 1

  Scenario: I check 20160101 string identity
    When I send a GET request to "/api/v2/20160101/user_chats/counts?group_by=date_created"
    Then the JSON node "data.nested[0].id" should be equal to "2000-01-02"
    And the JSON node "data.nested[0].value" should not exist
    And the JSON node "data.nested[0].type" should be equal to "date_created"
    And the JSON node "data.nested[0].title" should be equal to "2000-01-02"
    And the JSON node "data.nested[0].count" should be equal to 1

  Scenario: I check 20170401 int identity
    When I send a GET request to "/api/v2/20170401/user_chats/counts?group_by=agent"
    Then the JSON node "data.nested[0].id" should be equal to "{agent}"
    And the JSON node "data.nested[0].type" should be equal to "agent"
    And the JSON node "data.nested[0].title" should be equal to "Agent Agent"
    And the JSON node "data.nested[0].count" should be equal to 1

  Scenario: I check 20170401 string identity
    When I send a GET request to "/api/v2/20170401/user_chats/counts?group_by=date_created"
    Then the JSON node "data.nested[0].id" should be null
    And the JSON node "data.nested[0].value" should be equal to "2000-01-02"
    And the JSON node "data.nested[0].type" should be equal to "date_created"
    And the JSON node "data.nested[0].title" should be equal to "2000-01-02"
    And the JSON node "data.nested[0].count" should be equal to 1

  Scenario: I check default count version
    When I send a GET request to "/api/v2/user_chats/counts?group_by=date_created"
    Then the JSON node "data.id" should exist
    And the JSON node "data.value" should exist
    And the JSON node "data.nested[0].id" should exist
    And the JSON node "data.nested[0].value" should exist
