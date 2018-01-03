Feature: Api endpoints providing sideloading features

  Background:
    Given I'm authenticated as admin
    And the following Usergroup records exist:
      | #   | sys_name | Title   |
      | ug1 | group_1  | Group 1 |
      | ug2 | group_2  | Group 2 |
    And the following User records exist:
      | #  | Name    | Usergroups     |
      | u1 | User 1  | [{ug1}, {ug2}] |
      | u2 | User 2  | [{ug1}, {ug2}] |
    And the following Agent records exist:
      | #  | Name    | Usergroups     |
      | a1 | Agent 1 | [{ug1}, {ug2}] |
    And only the following Ticket records exist:
      | #  | Subject  | Person | Agent |
      | t1 | Ticket 1 | {u1}   | {a1}  |
      | t2 | Ticket 2 | {u2}   | {a1}  |

  Scenario: I'm loading tickets list with sideloading
    When I send a GET request to "/api/v2/tickets?include=person"
    Then the response should be in JSON
    And the JSON node "linked.person" should have 3 elements

  Scenario: I'm loading tickets list with sideloading (several sideloading keys)
    When I send a GET request to "/api/v2/tickets?include=person,usergroup"
    Then the response should be in JSON
    And the JSON node "linked.person" should have 3 elements
    And the JSON node "linked.usergroup" should have 2 elements

  Scenario: I'm loading tickets list with wrong sideloading key
    When I send a GET request to "/api/v2/tickets?include=person,foobar"
    Then the response should be in JSON
    And the JSON node "linked.person" should have 3 elements
    And the JSON node "linked.foobar" should not exist
