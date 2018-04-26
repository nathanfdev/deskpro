@new
Feature: CRUD offset

  Background:
    Given there are no "Person" records
    And I'm authenticated as agent
    And the following User records exist:
      | #   | Name    |
      | u1  | User 1  |
      | u2  | User 2  |
      | u3  | User 3  |
      | u4  | User 4  |
      | u5  | User 5  |
      | u6  | User 6  |
      | u7  | User 7  |
      | u8  | User 8  |
      | u9  | User 9  |
      | u10 | User 10 |
    And only the following Ticket records exist:
      | #   | Subject   | Status         | Agent   |
      | t1  | Ticket 1  | awaiting_agent | {agent} |
      | t2  | Ticket 2  | awaiting_agent | {agent} |
      | t3  | Ticket 3  | awaiting_agent | {agent} |
      | t4  | Ticket 4  | awaiting_agent | {agent} |
      | t5  | Ticket 5  | awaiting_agent | {agent} |
      | t6  | Ticket 6  | awaiting_agent | {agent} |
      | t7  | Ticket 7  | awaiting_agent | {agent} |
      | t8  | Ticket 8  | awaiting_agent | {agent} |
      | t9  | Ticket 9  | awaiting_agent | {agent} |
      | t10 | Ticket 10 | awaiting_agent | {agent} |
    And only the following LegacyTicketFilter records exist:
      | #  | Title    | Is Enabled | Is Global | Sys Name | Display Order | Terms                                                                                                                                                                        |
      | f1 | Filter 1 | 1          | 1         | agent    | 1             | [{"type":"agent","op":"is","options":{"agent":"-1"}},{"type":"status","op":"is","options":{"status":"awaiting_agent"}},{"type":"is_hold","op":"is","options":{"is_hold":0}}] |
    And I re-fill ticket search table

  Scenario Outline: I get crud list w/o offset
    When I send a GET request to "/api/v2/<endpoint>&order_dir=asc"
    Then the JSON node "data" should have 10 elements

    Examples:
      | endpoint                     |
      | people?is_agent=0            |
      | tickets?                     |
      | ticket_filters/{f1}/tickets? |

  Scenario Outline: I get crud list w/ offset and default count
    When I send a GET request to "/api/v2/<endpoint>&order_dir=asc&offset=5"
    Then the JSON node "data" should have 5 elements
    And the JSON node "data[0].id" should be equal to "{<id_pref>6}"
    And the JSON node "meta.pagination.per_page" should be equal to 10
    And the JSON node "meta.pagination.total" should be equal to 10
    And the JSON node "meta.pagination.offset" should be equal to 5

    Examples:
      | endpoint                     | id_pref |
      | people?is_agent=0            | u       |
      | tickets?                     | t       |
      | ticket_filters/{f1}/tickets?order_by=ticket.date_created | t       |

  Scenario Outline: I get crud list w/ offset and count
    When I send a GET request to "/api/v2/<endpoint>&order_dir=asc&offset=5&count=2"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{<id_pref>6}"
    And the JSON node "data[1].id" should be equal to "{<id_pref>7}"
    And the JSON node "meta.pagination.per_page" should be equal to 2
    And the JSON node "meta.pagination.total" should be equal to 10
    And the JSON node "meta.pagination.offset" should be equal to 5

    Examples:
      | endpoint                     | id_pref |
      | people?is_agent=0            | u       |
      | tickets?                     | t       |
      | ticket_filters/{f1}/tickets?order_by=ticket.date_created | t       |
