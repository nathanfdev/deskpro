Feature: /ticket_filter_sets endpoint
  To CRUD DeskPRO legacy ticket filter sets
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

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
    And the JSON node "data[0].filters" should have 10 elements

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].title" should be equal to "All tickets"
    And the JSON node "data[1].display_order" should be equal to 2
    And the JSON node "data[1].is_default" should be equal to 1
    And the JSON node "data[1].filters" should have 5 elements

    And the JSON node "data[2].id" should be equal to 3
    And the JSON node "data[2].title" should be equal to "Custom filters"
    And the JSON node "data[2].display_order" should be equal to 3
    And the JSON node "data[2].is_default" should be equal to 1
    And the JSON node "data[2].filters" should have 1 elements

    And the JSON node "linked" should have 0 elements

  Scenario: same as above but with sideloading
    When I send a GET request to "/api/v2/ticket_filter_sets?include=legacy_ticket_filter"
    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "linked.legacy_ticket_filter" should exist
    And the JSON node "linked.legacy_ticket_filter.1.id" should be equal to 1
    And the JSON node "linked.legacy_ticket_filter.1.title" should be equal to "My Tickets"
    And the JSON node "linked.legacy_ticket_filter.15.id" should be equal to 15
    And the JSON node "linked.legacy_ticket_filter.15.title" should be equal to "All (Hold)"
    And the JSON node "linked.legacy_ticket_filter.16.id" should be equal to 16
    And the JSON node "linked.legacy_ticket_filter.16.title" should be equal to "My custom filter"

  Scenario: I get filter_set w/o sideloading
    When I send a GET request to "/api/v2/ticket_filter_sets/3"
    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.title" should be equal to "Custom filters"
    And the JSON node "data.display_order" should be equal to 3
    And the JSON node "data.is_default" should be equal to 1
    And the JSON node "data.filters" should have 1 elements

    And the JSON node "linked" should have 0 elements

  Scenario: I get filter_set with sideloading
    When I send a GET request to "/api/v2/ticket_filter_sets/3?include=legacy_ticket_filter"

    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "linked.legacy_ticket_filter.16.id" should be equal to 16
    And the JSON node "linked.legacy_ticket_filter.16.title" should be equal to "My custom filter"

  Scenario: I get related filters for awaiting agent filter set
    When I send a GET request to "/api/v2/ticket_filter_sets/1/filters"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 10 elements
    And the JSON node "data[4].id" should be equal to 5
    And the JSON node "data[4].title" should be equal to "All"
    And the JSON node "data[9].id" should be equal to 15
    And the JSON node "data[9].title" should be equal to "All (Hold)"
    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].title" should be equal to "My Team's Tickets"
    And the JSON node "data[6].id" should be equal to 12
    And the JSON node "data[6].title" should be equal to "My Team's Tickets (Hold)"
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].title" should be equal to "My Tickets"
    And the JSON node "data[5].id" should be equal to 11
    And the JSON node "data[5].title" should be equal to "My Tickets (Hold)"

  Scenario: I get related filters for all tickets filter set
    When I send a GET request to "/api/v2/ticket_filter_sets/2/filters"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 5 elements
    And the JSON node "data[2].id" should be equal to 8
    And the JSON node "data[2].title" should be equal to "Archived"
    And the JSON node "data[0].id" should be equal to 6
    And the JSON node "data[0].title" should be equal to "Awaiting User"
    And the JSON node "data[4].id" should be equal to 10
    And the JSON node "data[4].title" should be equal to "Deleted"
    And the JSON node "data[1].id" should be equal to 7
    And the JSON node "data[1].title" should be equal to "Resolved"
    And the JSON node "data[3].id" should be equal to 9
    And the JSON node "data[3].title" should be equal to "Spam"

  Scenario: I get related filters for custom filter set
    When I send a GET request to "/api/v2/ticket_filter_sets/3/filters"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 16
    And the JSON node "data[0].title" should be equal to "My custom filter"
