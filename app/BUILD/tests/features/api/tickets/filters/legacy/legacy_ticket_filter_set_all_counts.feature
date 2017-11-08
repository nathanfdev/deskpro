@new
Feature: /ticket_filter_sets/all/counts endpoint
  To ticket filters grouping count
  As an API user
  I want to check endpoint

  Background:
    Given no Person records exist
    And I'm authenticated as admin
    And the following User records exist:
      | #  | Name   | Email              |
      | u1 | User 1 | user_1@deskpro.dev |
      | u2 | User 2 | user_2@deskpro.dev |
    And only the following LegacyTicketFilter records exist:
      | #  | Title           | Is Enabled | Is Global | Sys Name | Terms                                                      | Display Order |
      | f1 | Custom filter 1 | 1          | 1         | NULL     | [{"type":"agent","op":"is","options":{"agent":"-1"}}]      | 1             |
      | f2 | Custom filter 2 | 1          | 1         | NULL     | [{"type":"agent","op":"is","options":{"agent":"~admin~"}}] | 2             |
    And only the following Ticket records exist:
      | #  | Subject  | Person | Agent   |
      | t1 | Ticket 1 | {u1}   | NULL    |
      | t2 | Ticket 2 | {u1}   | {admin} |
      | t3 | Ticket 3 | {u1}   | {admin} |
      | t4 | Ticket 4 | {u2}   | NULL    |
      | t5 | Ticket 5 | {u2}   | NULL    |
      | t6 | Ticket 6 | {u2}   | {admin} |
    And I re-fill ticket search table

  Scenario: I retrieve list of all ticket filter sets counts
    When I send a GET request to "/api/v2/ticket_filter_sets/all/counts"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].count" should be equal to 0
    And the JSON node "data[0].type" should be equal to "ticket_filter_set"
    And the JSON node "data[0].title" should be equal to "Awaiting agent"
    And the JSON node "data[0].grouped_by" should be equal to 0
    And the JSON node "data[0].nested" should have 0 elements

    And the JSON node "data[1].count" should be equal to 0
    And the JSON node "data[1].type" should be equal to "ticket_filter_set"
    And the JSON node "data[1].title" should be equal to "All tickets"
    And the JSON node "data[1].grouped_by" should be equal to 0
    And the JSON node "data[1].nested" should have 0 elements

    And the JSON node "data[2].count" should be equal to 6
    And the JSON node "data[2].type" should be equal to "ticket_filter_set"
    And the JSON node "data[2].title" should be equal to "Custom filters"
    And the JSON node "data[2].grouped_by" should be equal to "filter"
    And the JSON node "data[2].nested" should have 2 elements

    And the JSON node "data[2].nested[0].title" should be equal to "Custom filter 1"
    And the JSON node "data[2].nested[0].type" should be equal to "filter"
    And the JSON node "data[2].nested[0].count" should be equal to 3
    And the JSON node "data[2].nested[0].grouped_by" should be equal to "filter"
    And the JSON node "data[2].nested[0].nested" should have 0 elements

    And the JSON node "data[2].nested[1].title" should be equal to "Custom filter 2"
    And the JSON node "data[2].nested[1].type" should be equal to "filter"
    And the JSON node "data[2].nested[1].count" should be equal to 3
    And the JSON node "data[2].nested[1].grouped_by" should be equal to "filter"
    And the JSON node "data[2].nested[1].nested" should have 0 elements

  Scenario: I group by person
    When I send a GET request to "/api/v2/ticket_filter_sets/all/counts?group_by[{f1}]=person&group_by[{f2}]=person"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data[2].nested[0].title" should be equal to "Custom filter 1"
    And the JSON node "data[2].nested[0].type" should be equal to "filter"
    And the JSON node "data[2].nested[0].count" should be equal to 3
    And the JSON node "data[2].nested[0].grouped_by" should be equal to "person"
    And the JSON node "data[2].nested[0].nested" should have 2 elements

    And the JSON node "data[2].nested[1].title" should be equal to "Custom filter 2"
    And the JSON node "data[2].nested[1].type" should be equal to "filter"
    And the JSON node "data[2].nested[1].count" should be equal to 3
    And the JSON node "data[2].nested[1].grouped_by" should be equal to "person"
    And the JSON node "data[2].nested[1].nested" should have 2 elements

    And the JSON node "data[2].nested[1].nested[0].type" should be equal to "person"
    And the JSON node "data[2].nested[1].nested[0].id" should be equal to "{u1}"
    And the JSON node "data[2].nested[1].nested[0].title" should be equal to "User 1"
    And the JSON node "data[2].nested[1].nested[0].count" should be equal to 2
    And the JSON node "data[2].nested[1].nested[0].nested" should have 0 element
