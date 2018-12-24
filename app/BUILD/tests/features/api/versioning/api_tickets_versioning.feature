@new
Feature: I check tickets versioning

  Background:
    Given I'm authenticated as agent
    And only the following TicketStatus records exist:
      | #   | StatusType     | SysId        | Title    |
      | ts1 | hidden         | spam         | Spam     |
      | ts2 | hidden         | deleted      | Deleted  |
    And only the following Ticket records exist:
      | #  | Subject  | Status         | TicketStatus |
      | t1 | Ticket 1 | awaiting_agent |              |
      | t2 | Ticket 2 | awaiting_user  |              |
      | t3 | Ticket 3 | archived       |              |
      | t4 | Ticket 4 | resolved       |              |
      | t5 | Ticket 5 | hidden         |              |
      | t6 | Ticket 6 | hidden         | {ts1}        |
      | t7 | Ticket 7 | hidden         | {ts2}        |

  Scenario: I check 20160101 version
    When I send a GET request to "/api/v2/20160101/tickets?order_by=id&order_dir=asc&status[]=awaiting_agent&status[]=awaiting_user&status[]=archived&status[]=resolved&status[]=hidden"
    Then the response status code should be 200
    And the JSON node "data[0].status" should be equal to "awaiting_agent"
    And the JSON node "data[0].hidden_status" should be null
    And the JSON node "data[1].status" should be equal to "awaiting_user"
    And the JSON node "data[1].hidden_status" should be null
    And the JSON node "data[2].status" should be equal to "archived"
    And the JSON node "data[2].hidden_status" should be null
    And the JSON node "data[3].status" should be equal to "resolved"
    And the JSON node "data[3].hidden_status" should be null
    And the JSON node "data[4].status" should be equal to "hidden"
    And the JSON node "data[4].hidden_status" should be null
    And the JSON node "data[5].status" should be equal to "hidden"
    And the JSON node "data[5].hidden_status" should be equal to "spam"
    And the JSON node "data[6].status" should be equal to "hidden"
    And the JSON node "data[6].hidden_status" should be equal to "deleted"

  Scenario: I check 20170401 version
    When I send a GET request to "/api/v2/20170401/tickets?order_by=id&order_dir=asc&status[]=awaiting_agent&status[]=awaiting_user&status[]=archived&status[]=resolved&status[]=hidden"
    Then the response status code should be 200
    And the JSON node "data[0].status" should be equal to "awaiting_agent"
    And the JSON node "data[0].hidden_status" should not exist
    And the JSON node "data[1].status" should be equal to "awaiting_user"
    And the JSON node "data[1].hidden_status" should not exist
    And the JSON node "data[2].status" should be equal to "archived"
    And the JSON node "data[2].hidden_status" should not exist
    And the JSON node "data[3].status" should be equal to "resolved"
    And the JSON node "data[3].hidden_status" should not exist
    And the JSON node "data[4].status" should be equal to "hidden.deleted"
    And the JSON node "data[4].hidden_status" should not exist
    And the JSON node "data[5].status" should be equal to "hidden.spam"
    And the JSON node "data[5].hidden_status" should not exist
    And the JSON node "data[6].status" should be equal to "hidden.deleted"
    And the JSON node "data[6].hidden_status" should not exist

  Scenario: I check 20170405 version
    When I send a GET request to "/api/v2/20170405/tickets?order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data[0].status" should be equal to "awaiting_agent"
    And the JSON node "data[0].hidden_status" should not exist

  Scenario: I check default version
    When I send a GET request to "/api/v2/tickets?order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data[0].status" should be equal to "awaiting_agent"
    And the JSON node "data[0].hidden_status" should not exist
