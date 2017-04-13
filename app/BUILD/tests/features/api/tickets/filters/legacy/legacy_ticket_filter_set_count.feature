@new
Feature: /ticket_filter_sets/{id}/count endpoint
  To ticket filters grouping count
  As an API user
  I want to check endpoint

  Background:
    Given I'm authenticated as admin
    And "user_1@deskpro.dev" user exists
    And "user_2@deskpro.dev" user exists
    And only the following LegacyTicketFilter records exist:
      | #  | Title           | Is Enabled | Is Global | Sys Name | Terms                                                      | Display Order |
      | f1 | Custom filter 1 | 1          | 1         | NULL     | [{"type":"agent","op":"is","options":{"agent":"-1"}}]      | 1             |
      | f2 | Custom filter 2 | 1          | 1         | NULL     | [{"type":"agent","op":"is","options":{"agent":"~admin~"}}] | 2             |
    And only the following Ticket records exist:
      | #  | Subject  | Person               | Agent   |
      | t1 | Ticket 1 | {user_1@deskpro.dev} | NULL    |
      | t2 | Ticket 2 | {user_1@deskpro.dev} | {admin} |
      | t3 | Ticket 3 | {user_1@deskpro.dev} | {admin} |
      | t4 | Ticket 4 | {user_2@deskpro.dev} | NULL    |
      | t5 | Ticket 5 | {user_2@deskpro.dev} | NULL    |
      | t6 | Ticket 6 | {user_2@deskpro.dev} | {admin} |
    And I re-fill ticket search table

  Scenario: I retrieve list of all ticket filter set counts
    When I send a GET request to "/api/v2/ticket_filter_sets/3/count"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.count" should be equal to 6
    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.type" should be equal to "ticket_filter_set"
    And the JSON node "data.title" should be equal to "Custom filters"
    And the JSON node "data.grouped_by" should be equal to "filter"
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].id" should be equal to "{f1}"
    And the JSON node "data.nested[0].title" should be equal to "Custom filter 1"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to 0
    And the JSON node "data.nested[0].nested" should have 0 elements

    And the JSON node "data.nested[1].id" should be equal to "{f2}"
    And the JSON node "data.nested[1].title" should be equal to "Custom filter 2"
    And the JSON node "data.nested[1].type" should be equal to "filter"
    And the JSON node "data.nested[1].count" should be equal to 3
    And the JSON node "data.nested[1].grouped_by" should be equal to 0
    And the JSON node "data.nested[1].nested" should have 0 elements

  Scenario: I group by person
    When I send a GET request to "/api/v2/ticket_filter_sets/3/count?group_by[{f1}]=person"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.count" should be equal to 6
    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.type" should be equal to "ticket_filter_set"
    And the JSON node "data.title" should be equal to "Custom filters"
    And the JSON node "data.grouped_by" should be equal to "filter"
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].id" should be equal to "{f1}"
    And the JSON node "data.nested[0].title" should be equal to "Custom filter 1"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to "person"
    And the JSON node "data.nested[0].nested" should have 2 elements

    And the JSON node "data.nested[0].nested[0].id" should be equal to "{user_1@deskpro.dev}"
    And the JSON node "data.nested[0].nested[0].type" should be equal to "person"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "User User"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data.nested[0].nested[1].id" should be equal to "{user_2@deskpro.dev}"
    And the JSON node "data.nested[0].nested[1].count" should be equal to 1
