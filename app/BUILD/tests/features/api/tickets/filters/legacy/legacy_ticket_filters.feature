@ticket-filters
Feature: /ticket_filters endpoint
  To legacy ticket filters
  As a developer
  I want to check endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve list of ticket filters
    When I send a GET request to "/api/v2/ticket_filters"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 10 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].title" should be equal to "My Tickets"
    And the JSON node "data[0].display_order" should be equal to 1
    And the JSON node "data[0].ticket_filter_set" should be equal to 1
    And the JSON node "data[0].filter_views" should exist
    And the JSON node "data[0].filter_preferences" should exist
    And the JSON node "data[0].date_created" should exist
    And the JSON node "data[0].date_updated" should exist

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].title" should be equal to "My Team's Tickets"
    And the JSON node "data[1].display_order" should be equal to 2
    And the JSON node "data[1].ticket_filter_set" should be equal to 1

    And the JSON node "data[2].id" should be equal to 3
    And the JSON node "data[2].title" should be equal to "Tickets I Follow"
    And the JSON node "data[2].display_order" should be equal to 3
    And the JSON node "data[2].ticket_filter_set" should be equal to 1
    And the JSON node "linked" should have 0 elements

  Scenario: Same as previous but with sideloading
    When I send a GET request to "/api/v2/ticket_filters?include=ticket_filter_set"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "linked.ticket_filter_set" should exist
    And the JSON node "linked.ticket_filter_set.1.id" should be equal to 1
    And the JSON node "linked.ticket_filter_set.1.title" should be equal to "Awaiting agent"
    And the JSON node "linked.ticket_filter_set.2.id" should be equal to 2
    And the JSON node "linked.ticket_filter_set.2.title" should be equal to "All tickets"


  Scenario: I get ticket filter
    When I send a GET request to "/api/v2/ticket_filters/2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.id" should be equal to 2
    And the JSON node "data.title" should be equal to "My Team's Tickets"
    And the JSON node "data.display_order" should be equal to 2
    And the JSON node "data.ticket_filter_set" should be equal to 1
    And the JSON node "data.term" should have 3 elements
    And the JSON node "data.term[0].type" should be equal to "agent_team"
    And the JSON node "data.term[1].type" should be equal to "status"
    And the JSON node "data.term[2].type" should be equal to "is_hold"

  Scenario: I retrieve list of filter's tickets
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filters/1/tickets"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to 2
    And the JSON node "data[0].subject" should be equal to "Ticket #1"
    And the JSON node "data[1].id" should be equal to 4
    And the JSON node "data[1].subject" should be equal to "Ticket #3"
