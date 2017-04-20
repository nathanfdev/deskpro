Feature: /ticket_filter_sets/all/counts endpoint
  To ticket filters grouping count
  As an API user
  I want to check endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve list of all ticket filter sets counts
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filter_sets/all/counts"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].count" should be equal to 6
    And the JSON node "data[0].type" should be equal to "ticket_filter_set"
    And the JSON node "data[0].title" should be equal to "Awaiting agent"
    And the JSON node "data[0].grouped_by" should be equal to "filter"
    And the JSON node "data[0].nested" should have 10 elements

    And the JSON node "data[0].nested[4].title" should be equal to "All"
    And the JSON node "data[0].nested[4].type" should be equal to "filter"
    And the JSON node "data[0].nested[4].count" should be equal to 3
    And the JSON node "data[0].nested[4].grouped_by" should be equal to 0
    And the JSON node "data[0].nested[4].nested" should have 0 elements

    And the JSON node "data[0].nested[9].title" should be equal to "All (Hold)"
    And the JSON node "data[0].nested[9].type" should be equal to "filter"
    And the JSON node "data[0].nested[9].count" should be equal to 0
    And the JSON node "data[0].nested[9].grouped_by" should be equal to 0
    And the JSON node "data[0].nested[9].nested" should have 0 elements

    And the JSON node "data[1].count" should be equal to 1
    And the JSON node "data[1].type" should be equal to "ticket_filter_set"
    And the JSON node "data[1].title" should be equal to "All tickets"
    And the JSON node "data[1].grouped_by" should be equal to "filter"
    And the JSON node "data[1].nested" should have 5 elements

    And the JSON node "data[1].nested[2].title" should be equal to "Archived"
    And the JSON node "data[1].nested[2].type" should be equal to "filter"
    And the JSON node "data[1].nested[2].count" should be equal to 0
    And the JSON node "data[1].nested[2].grouped_by" should be equal to 0
    And the JSON node "data[1].nested[2].nested" should have 0 elements

    And the JSON node "data[1].nested[0].title" should be equal to "Awaiting User"
    And the JSON node "data[1].nested[0].type" should be equal to "filter"
    And the JSON node "data[1].nested[0].count" should be equal to 1
    And the JSON node "data[1].nested[0].grouped_by" should be equal to 0
    And the JSON node "data[1].nested[0].nested" should have 0 elements

    And the JSON node "data[2].count" should be equal to 0
    And the JSON node "data[2].type" should be equal to "ticket_filter_set"
    And the JSON node "data[2].title" should be equal to "Custom filters"
    And the JSON node "data[2].grouped_by" should be equal to "filter"
    And the JSON node "data[2].nested" should have 1 element

    And the JSON node "data[2].nested[0].title" should be equal to "My custom filter"
    And the JSON node "data[2].nested[0].type" should be equal to "filter"
    And the JSON node "data[2].nested[0].count" should be equal to 0
    And the JSON node "data[2].nested[0].grouped_by" should be equal to 0
    And the JSON node "data[2].nested[0].nested" should have 0 elements

  Scenario: I group by person
    When I send a GET request to "/api/v2/ticket_filter_sets/all/counts?group_by[5]=person"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].count" should be equal to 6
    And the JSON node "data[0].type" should be equal to "ticket_filter_set"
    And the JSON node "data[0].title" should be equal to "Awaiting agent"
    And the JSON node "data[0].grouped_by" should be equal to "filter"
    And the JSON node "data[0].nested" should have 10 elements

    And the JSON node "data[0].nested[4].title" should be equal to "All"
    And the JSON node "data[0].nested[4].type" should be equal to "filter"
    And the JSON node "data[0].nested[4].count" should be equal to 3
    And the JSON node "data[0].nested[4].grouped_by" should be equal to "person"
    And the JSON node "data[0].nested[4].nested" should have 1 elements

    And the JSON node "data[0].nested[4].nested[0].type" should be equal to "person"
    And the JSON node "data[0].nested[4].nested[0].title" should be equal to "Ganon User"
    And the JSON node "data[0].nested[4].nested[0].count" should be equal to 3
    And the JSON node "data[0].nested[4].nested[0].nested" should have 0 elements
