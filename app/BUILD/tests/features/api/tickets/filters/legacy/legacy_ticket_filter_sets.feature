@new
Feature: /ticket_filter_sets endpoint
  To CRUD DeskPRO legacy ticket filter sets
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin
    And only the following LegacyTicketFilter records exist:
      | #  | Title                | Is Enabled | Is Global | Sys Name          | Terms                                                 | Display Order |
      | f1 | Custom filter 1      | 1          | 1         | NULL              | [{"type":"agent","op":"is","options":{"agent":"-1"}}] | 1             |
      | f2 | Custom filter 2      | 1          | 1         | NULL              | [{"type":"agent","op":"is","options":{"agent":"-1"}}] | 2             |
      | f3 | All tickets filter 1 | 1          | 1         | archive_filter_1  | [{"type":"agent","op":"is","options":{"agent":"-1"}}] | 3             |
      | f4 | All tickets filter 2 | 1          | 1         | archive_filter_2  | [{"type":"agent","op":"is","options":{"agent":"-1"}}] | 4             |
      | f5 | Awaiting filter 1    | 1          | 1         | awaiting_filter_1 | [{"type":"agent","op":"is","options":{"agent":"-1"}}] | 5             |
      | f6 | Awaiting filter 2    | 1          | 1         | awaiting_filter_2 | [{"type":"agent","op":"is","options":{"agent":"-1"}}] | 6             |
    And I re-fill ticket search table

  Scenario: I retrieve list of ticket filter sets
    When I send a GET request to "/api/v2/ticket_filter_sets"
    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].title" should be equal to "Awaiting agent"
    And the JSON node "data[0].display_order" should be equal to 1
    And the JSON node "data[0].is_default" should be equal to 1
    And the JSON node "data[0].private_agent" should exist
    And the JSON node "data[0].shared_agents" should exist
    And the JSON node "data[0].filters" should have 2 elements
    And the JSON node "data[0].filters[0]" should be equal to "{f5}"
    And the JSON node "data[0].filters[1]" should be equal to "{f6}"

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].title" should be equal to "All tickets"
    And the JSON node "data[1].display_order" should be equal to 2
    And the JSON node "data[1].is_default" should be equal to 1
    And the JSON node "data[1].filters" should have 2 elements
    And the JSON node "data[1].filters[0]" should be equal to "{f3}"
    And the JSON node "data[1].filters[1]" should be equal to "{f4}"

    And the JSON node "data[2].id" should be equal to 3
    And the JSON node "data[2].title" should be equal to "Custom filters"
    And the JSON node "data[2].display_order" should be equal to 3
    And the JSON node "data[2].is_default" should be equal to 1
    And the JSON node "data[2].filters" should have 2 elements
    And the JSON node "data[2].filters[0]" should be equal to "{f1}"
    And the JSON node "data[2].filters[1]" should be equal to "{f2}"

    And the JSON node "linked" should have 0 elements

  Scenario: same as above but with sideloading
    When I send a GET request to "/api/v2/ticket_filter_sets?include=legacy_ticket_filter"
    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "linked.legacy_ticket_filter" should exist
    And the JSON node "linked.legacy_ticket_filter.{f1}.id" should be equal to "{f1}"
    And the JSON node "linked.legacy_ticket_filter.{f1}.title" should be equal to "Custom filter 1"
    And the JSON node "linked.legacy_ticket_filter.{f3}.id" should be equal to "{f3}"
    And the JSON node "linked.legacy_ticket_filter.{f3}.title" should be equal to "All tickets filter 1"
    And the JSON node "linked.legacy_ticket_filter.{f5}.id" should be equal to "{f5}"
    And the JSON node "linked.legacy_ticket_filter.{f5}.title" should be equal to "Awaiting filter 1"

  Scenario: I get filter_set w/o sideloading
    When I send a GET request to "/api/v2/ticket_filter_sets/3"
    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.title" should be equal to "Custom filters"
    And the JSON node "data.display_order" should be equal to 3
    And the JSON node "data.is_default" should be equal to 1
    And the JSON node "data.filters" should have 2 elements

    And the JSON node "linked" should have 0 elements

  Scenario: I get filter_set with sideloading
    When I send a GET request to "/api/v2/ticket_filter_sets/3?include=legacy_ticket_filter"

    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "linked.legacy_ticket_filter.{f1}.id" should exist
    And the JSON node "linked.legacy_ticket_filter.{f2}.id" should exist

  Scenario: I get related filters for awaiting agent filter set
    When I send a GET request to "/api/v2/ticket_filter_sets/1/filters"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{f5}"
    And the JSON node "data[0].title" should be equal to "Awaiting filter 1"
    And the JSON node "data[1].id" should be equal to "{f6}"
    And the JSON node "data[1].title" should be equal to "Awaiting filter 2"

  Scenario: I get related filters for all tickets filter set
    When I send a GET request to "/api/v2/ticket_filter_sets/2/filters"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{f3}"
    And the JSON node "data[0].title" should be equal to "All tickets filter 1"
    And the JSON node "data[1].id" should be equal to "{f4}"
    And the JSON node "data[1].title" should be equal to "All tickets filter 2"

  Scenario: I get related filters for custom filter set
    When I send a GET request to "/api/v2/ticket_filter_sets/3/filters"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{f1}"
    And the JSON node "data[0].title" should be equal to "Custom filter 1"
    And the JSON node "data[1].id" should be equal to "{f2}"
    And the JSON node "data[1].title" should be equal to "Custom filter 2"
