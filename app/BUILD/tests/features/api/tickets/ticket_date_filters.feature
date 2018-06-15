@new
Feature: /tickets endpoint
  To check date filters

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  | Agent   | Date Created        | Date Resolved       | Date Last Agent Reply | Date Last User Reply |
      | t1 | Ticket 1 | {admin} | 2018-03-01 00:00:00 | 2018-06-01 00:00:00 | 2018-04-01 00:00:00   | 2018-05-01 00:00:00  |
      | t2 | Ticket 2 | {admin} | 2018-03-02 00:00:00 | 2018-06-02 00:00:00 | 2018-04-02 00:00:00   | 2018-05-02 00:00:00  |
      | t3 | Ticket 3 | {admin} | 2018-03-03 00:00:00 | 2018-06-03 00:00:00 | 2018-04-03 00:00:00   | 2018-05-03 00:00:00  |
      | t4 | Ticket 4 | {admin} | 2018-03-04 00:00:00 | 2018-06-04 00:00:00 | 2018-04-04 00:00:00   | 2018-05-04 00:00:00  |
      | t5 | Ticket 5 | {admin} | 2018-03-05 00:00:00 | 2018-06-05 00:00:00 | 2018-04-05 00:00:00   | 2018-05-05 00:00:00  |

  Scenario: I check date created filter
    When I send a GET request to "/api/v2/tickets?date_created=2018-03-03&order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t3}"
    And the JSON node "data[1].id" should be equal to "{t4}"
    And the JSON node "data[2].id" should be equal to "{t5}"

  Scenario: I check between filter
    When I send a GET request to "/api/v2/tickets?date_created=2018-03-02/2018-03-04&order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t2}"
    And the JSON node "data[1].id" should be equal to "{t3}"
    And the JSON node "data[2].id" should be equal to "{t4}No handler is capable of handling"

  Scenario: I check date resolved filter
    When I send a GET request to "/api/v2/tickets?date_resolved=<2018-06-03&order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"

  Scenario: I check date resolved filter
    When I send a GET request to "/api/v2/tickets?date_last_agent_reply=<=2018-04-03&order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t2}"
    And the JSON node "data[2].id" should be equal to "{t3}"


  Scenario: I check date resolved filter
    When I send a GET request to "/api/v2/tickets?date_last_user_reply=>=2018-05-03&order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{t3}"
    And the JSON node "data[1].id" should be equal to "{t4}"
    And the JSON node "data[2].id" should be equal to "{t5}"
