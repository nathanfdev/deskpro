@new
Feature: /ticket_filters endpoint
  To legacy ticket filters
  As an API user
  I want to check endpoint

  Background:
    Given I'm authenticated as admin
    And no LegacyTicketFilter records exist

  Scenario: I retrieve list of ticket filters
    Given only the following LegacyTicketFilter records exist:
      | #  | Title    | Is Enabled | Is Global | Sys Name    | Display Order |
      | f1 | Filter 1 | 1          | 1         | agent       | 1             |
      | f2 | Filter 2 | 1          | 1         | agent_team  | 2             |
      | f3 | Filter 3 | 1          | 1         | participant | 3             |

    When I send a GET request to "/api/v2/ticket_filters?count=100"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{f1}"
    And the JSON node "data[0].title" should be equal to "Filter 1"
    And the JSON node "data[0].sys_name" should be equal to "agent"
    And the JSON node "data[0].display_order" should be equal to 1
    And the JSON node "data[0].ticket_filter_set" should be equal to 1
    And the JSON node "data[0].filter_views" should exist
    And the JSON node "data[0].filter_preferences" should exist
    And the JSON node "data[0].date_created" should exist
    And the JSON node "data[0].date_updated" should exist

    And the JSON node "data[1].id" should be equal to "{f2}"
    And the JSON node "data[1].title" should be equal to "Filter 2"
    And the JSON node "data[1].sys_name" should be equal to "agent_team"
    And the JSON node "data[1].display_order" should be equal to 2
    And the JSON node "data[1].ticket_filter_set" should be equal to 1

    And the JSON node "data[2].id" should be equal to "{f3}"
    And the JSON node "data[2].title" should be equal to "Filter 3"
    And the JSON node "data[2].sys_name" should be equal to "participant"
    And the JSON node "data[2].display_order" should be equal to 3
    And the JSON node "data[2].ticket_filter_set" should be equal to 1
    And the JSON node "linked" should have 0 elements

  Scenario: Same as previous but with sideloading
    Given only the following LegacyTicketFilter records exist:
      | #  | Title    | Is Enabled | Is Global | Sys Name    | Display Order |
      | f1 | Filter 1 | 1          | 1         | agent       | 1             |
      | f2 | Filter 2 | 1          | 1         | agent_team  | 2             |
      | f3 | Filter 3 | 1          | 1         | participant | 3             |

    When I send a GET request to "/api/v2/ticket_filters?include=ticket_filter_set"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "linked.ticket_filter_set" should exist
    And the JSON node "linked.ticket_filter_set.1.id" should be equal to 1
    And the JSON node "linked.ticket_filter_set.1.title" should be equal to "Awaiting agent"

  Scenario: I get ticket filter
    Given only the following LegacyTicketFilter records exist:
      | #  | Title    | Is Enabled | Is Global | Sys Name | Display Order | Terms                                                                                                                                                                        |
      | f1 | Filter 1 | 1          | 1         | agent    | 1             | [{"type":"agent","op":"is","options":{"agent":"-1"}},{"type":"status","op":"is","options":{"status":"awaiting_agent"}},{"type":"is_hold","op":"is","options":{"is_hold":0}}] |

    When I send a GET request to "/api/v2/ticket_filters/{f1}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.id" should be equal to "{f1}"
    And the JSON node "data.title" should be equal to "Filter 1"
    And the JSON node "data.display_order" should be equal to 1
    And the JSON node "data.ticket_filter_set" should be equal to 1
    And the JSON node "data.term" should have 3 elements
    And the JSON node "data.term[0].type" should be equal to "agent"
    And the JSON node "data.term[1].type" should be equal to "status"
    And the JSON node "data.term[2].type" should be equal to "is_hold"

  Scenario: I try to get filter with sys_name = "problem_\d+"
    Given only the following LegacyTicketFilter records exist:
      | #  | Title    | Is Enabled | Is Global | Sys Name    | Display Order |
      | f1 | Filter 1 | 1          | 1         | problem_1   | 1             |

    When I send a GET request to "/api/v2/ticket_filters/{f1}"
    Then the response status code should be 404

  Scenario: I retrieve list of filter's tickets
    Given only the following LegacyTicketFilter records exist:
      | #  | Title    | Is Enabled | Is Global | Sys Name | Display Order | Terms                                                                                                                                                                        |
      | f1 | Filter 1 | 1          | 1         | agent    | 1             | [{"type":"agent","op":"is","options":{"agent":"-1"}},{"type":"status","op":"is","options":{"status":"awaiting_agent"}},{"type":"is_hold","op":"is","options":{"is_hold":0}}] |
    And only the following Ticket records exist:
      | #  | Subject  | Status         |
      | t1 | Ticket 1 | awaiting_agent |
      | t2 | Ticket 2 | awaiting_user  |
      | t3 | Ticket 3 | awaiting_agent |
    And I re-fill ticket search table

    When I send a GET request to "/api/v2/ticket_filters/{f1}/tickets"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t1}"
    And the JSON node "data[1].id" should be equal to "{t3}"

  Scenario: I retrieve list of filter's tickets with page and count
    Given only the following LegacyTicketFilter records exist:
      | #  | Title    | Is Enabled | Is Global | Sys Name | Display Order | Terms                                                                                                                                                                        |
      | f1 | Filter 1 | 1          | 1         | agent    | 1             | [{"type":"agent","op":"is","options":{"agent":"-1"}},{"type":"status","op":"is","options":{"status":"awaiting_agent"}},{"type":"is_hold","op":"is","options":{"is_hold":0}}] |
    And only the following Ticket records exist:
      | #  | Subject  | Status         |
      | t1 | Ticket 1 | awaiting_agent |
      | t2 | Ticket 2 | awaiting_user  |
      | t3 | Ticket 3 | awaiting_agent |
      | t4 | Ticket 4 | awaiting_agent |
      | t5 | Ticket 5 | awaiting_agent |
    And I re-fill ticket search table

    When I send a GET request to "/api/v2/ticket_filters/{f1}/tickets?page=2&count=2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{t4}"
    And the JSON node "data[1].id" should be equal to "{t5}"

  Scenario: I check filter permissions
    Given "agent@deskpro.dev" agent exists
    And only the following AgentTeam records exist:
      | #  | Name   |
      | t1 | Team 1 |
      | t2 | Team 2 |
    And the "{admin}" record "primary_team" prop is equal to "{t1}"
    And only the following LegacyTicketFilter records exist:
      | #  | Title    | Is Enabled | Person              | Is Global | Sys Name | Display Order | Agent Team |
      | f1 | Filter 1 | 1          | NULL                | 1         | NULL     | 1             | NULL       |
      | f2 | Filter 2 | 1          | {admin}             | 0         | NULL     | 1             | NULL       |
      | f3 | Filter 3 | 1          | {agent@deskpro.dev} | 0         | NULL     | 1             | NULL       |
      | f4 | Filter 4 | 1          | NULL                | 0         | NULL     | 1             | {t1}       |
      | f5 | Filter 5 | 1          | NULL                | 0         | NULL     | 1             | {t2}       |

    When I send a GET request to "/api/v2/ticket_filters"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{f1}"
    And the JSON node "data[1].id" should be equal to "{f2}"
    And the JSON node "data[2].id" should be equal to "{f4}"
