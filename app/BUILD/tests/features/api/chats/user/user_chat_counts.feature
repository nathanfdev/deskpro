@new
Feature: /user_chats/counts endpoint
  To retrieve DeskPRO agents
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as agent
    And "agent2@deskpro.dev" agent exists
    And "user@deskpro.dev" user exists
    And "user2@deskpro.dev" user exists
    And the following "Department" records exist:
      | #  | title           | is_chat_enabled |
      | d1 | ChatDepartment1 | 1               |
      | d2 | ChatDepartment2 | 1               |

    And only the following Chat records exist:
      | #  | subject | date_created | agent                | person              | department |
      | c1 | Chat1   | 2000-01-02   | {agent}              | {user}              | {d1}         |
      | c2 | Chat2   | 2000-01-02   | {agent}              | {user2@deskpro.dev} | {d2}         |
      | c3 | Chat3   | 2000-01-02   | {agent2@deskpro.dev} | {user}              | {d1}         |
      | c4 | Chat4   | 2000-01-03   | {agent}              | {user2@deskpro.dev} | {d2}         |
      | c5 | Chat5   | 2000-01-03   | {agent2@deskpro.dev} | {user}              | {d1}         |

  Scenario: I count chats without grouping
    When I send a GET request to "/api/v2/user_chats/counts"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 5
    And the JSON node "data.nested" should have 0 elements

  Scenario: I group by date created
    When I send a GET request to "/api/v2/user_chats/counts?group_by=date_created"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 5
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].id" should be equal to "2000-01-02"
    And the JSON node "data.nested[0].type" should be equal to "date_created"
    And the JSON node "data.nested[0].title" should be equal to "2000-01-02"
    And the JSON node "data.nested[0].count" should be equal to 3

    And the JSON node "data.nested[1].id" should be equal to "2000-01-03"
    And the JSON node "data.nested[1].type" should be equal to "date_created"
    And the JSON node "data.nested[1].title" should be equal to "2000-01-03"
    And the JSON node "data.nested[1].count" should be equal to 2

  Scenario: I group by and filter date created
    When I send a GET request to "/api/v2/user_chats/counts?group_by=date_created&created_from=2000-01-02&created_to=2000-01-02"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.nested" should have 1 element

    And the JSON node "data.nested[0].id" should be equal to "2000-01-02"
    And the JSON node "data.nested[0].count" should be equal to 3

  Scenario: I group by date period
    When I send a GET request to "/api/v2/user_chats/counts?group_by=date_period"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 5
    And the JSON node "data.nested" should have 1 element

    And the JSON node "data.nested[0].id" should be equal to "ever"
    And the JSON node "data.nested[0].type" should be equal to "date_period"
    And the JSON node "data.nested[0].title" should be equal to "Ever"
    And the JSON node "data.nested[0].count" should be equal to 5

  Scenario: I group by agent
    When I send a GET request to "/api/v2/user_chats/counts?group_by=agent"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 5
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].id" should be equal to "{agent}"
    And the JSON node "data.nested[0].type" should be equal to "agent"
    And the JSON node "data.nested[0].count" should be equal to 3

    And the JSON node "data.nested[1].id" should be equal to "{agent2@deskpro.dev}"
    And the JSON node "data.nested[1].type" should be equal to "agent"
    And the JSON node "data.nested[1].count" should be equal to 2

  Scenario: I filter and group by agent
    When I send a GET request to "/api/v2/user_chats/counts?group_by=agent&agent={agent2@deskpro.dev}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 2
    And the JSON node "data.nested" should have 1 element

    And the JSON node "data.nested[0].id" should be equal to "{agent2@deskpro.dev}"
    And the JSON node "data.nested[0].count" should be equal to 2

  Scenario: I group by department
    When I send a GET request to "/api/v2/user_chats/counts?group_by=department"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 5
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].id" should be equal to "{d1}"
    And the JSON node "data.nested[0].type" should be equal to "department"
    And the JSON node "data.nested[0].title" should be equal to "ChatDepartment1"
    And the JSON node "data.nested[0].count" should be equal to 3

    And the JSON node "data.nested[1].id" should be equal to "{d2}"
    And the JSON node "data.nested[1].type" should be equal to "department"
    And the JSON node "data.nested[1].title" should be equal to "ChatDepartment2"
    And the JSON node "data.nested[1].count" should be equal to 2

  Scenario: I filter and group by department
    When I send a GET request to "/api/v2/user_chats/counts?group_by=department&department={d2}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.count" should be equal to 2
    And the JSON node "data.nested" should have 1 element

    And the JSON node "data.nested[0].id" should be equal to "{d2}"
    And the JSON node "data.nested[0].count" should be equal to 2
