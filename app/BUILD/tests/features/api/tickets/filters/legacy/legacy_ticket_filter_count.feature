@new
Feature: /ticket_filters/{filter}/count endpoint
  To ticket filters grouping count
  As an API user
  I want to check endpoint

  Background:
    Given no Person records exist
    And I'm authenticated as admin
    And agent and user exist
    And only the following LegacyTicketFilter records exist:
      | #  | Title    | Is Enabled | Is Global | Sys Name | Display Order | Terms                                                                                                                                                                        |
      | f1 | Filter 1 | 1          | 1         | agent    | 1             | [{"type":"agent","op":"is","options":{"agent":"-1"}},{"type":"status","op":"is","options":{"status":"awaiting_agent"}},{"type":"is_hold","op":"is","options":{"is_hold":0}}] |
    And only the following Ticket records exist:
      | #  | Subject  | Status         | Person |
      | t1 | Ticket 1 | awaiting_agent | {user} |
      | t2 | Ticket 2 | awaiting_agent | {user} |
      | t3 | Ticket 3 | awaiting_agent | {user} |
      | t4 | Ticket 4 | awaiting_user  | {user} |
      | t5 | Ticket 5 | resolved       | {user} |
    And I re-fill ticket search table

  Scenario: I retrieve list of ticket filter count
    When I send a GET request to "/api/v2/ticket_filters/{f1}/count"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.id" should be equal to "{f1}"
    And the JSON node "data.title" should be equal to "Filter 1"
    And the JSON node "data.type" should be equal to "filter"
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.grouped_by" should be equal to 0
    And the JSON node "data.nested" should have 0 elements

  Scenario: I group by person
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filters/{f1}/count?group_by=person"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.id" should be equal to "{f1}"
    And the JSON node "data.title" should be equal to "Filter 1"
    And the JSON node "data.type" should be equal to "filter"
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.grouped_by" should be equal to "person"
    And the JSON node "data.nested" should have 1 element

    And the JSON node "data.nested[0].id" should be equal to "{user}"
    And the JSON node "data.nested[0].type" should be equal to "person"
    And the JSON node "data.nested[0].title" should be equal to "User User"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested" should have 0 elements
