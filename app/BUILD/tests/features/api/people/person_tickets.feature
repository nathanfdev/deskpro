Feature: /people/{id}/tickets endpoint

  Background:
    Given I'm authenticated as admin
    And agent and user exist
    And only the following Ticket records exist:
      | #  | Subject  | Person | Agent   |
      | t1 | Ticket 1 | {user} | {admin} |
      | t2 | Ticket 2 | NULL   | {agent} |
      | t3 | Ticket 3 | {user} | {agent} |
      | t4 | Ticket 4 | NULL   | {admin} |
      | t5 | Ticket 5 | {user} | NULL    |

  Scenario: I get user's tickets
    When I send a GET request to "/api/v2/people/{user}/tickets?order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t3}"
    And the JSON node "data[2].id" should be equal to "{t5}"

  Scenario: I get agent's tickets
    When I send a GET request to "/api/v2/people/{agent}/tickets?order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t2}"
    And the JSON node "data[1].id" should be equal to "{t3}"

  Scenario: I get admins's tickets
    When I send a GET request to "/api/v2/people/{admin}/tickets?order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t4}"
