@ticket-filters
Feature: /new/ticket_filter_sets/all/counts endpoint
  To ticket filters grouping count
  As a developer
  I want to check endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve list of all ticket filter sets counts
    When I send a GET request to "/api/v2/new/ticket_filter_sets/all/counts"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].count" should be equal to 3
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].type" should be equal to "ticket_filter_set"
    And the JSON node "data[0].title" should be equal to "Filter set 1"
    And the JSON node "data[0].grouped_by" should be equal to "filter"
    And the JSON node "data[0].nested" should have 2 elements

    And the JSON node "data[0].nested[0].count" should be equal to 3
    And the JSON node "data[0].nested[0].id" should be equal to 1
    And the JSON node "data[0].nested[0].type" should be equal to "filter"
    And the JSON node "data[0].nested[0].title" should be equal to "Filter 1"
    And the JSON node "data[0].nested[0].grouped_by" should be equal to 0

    And the JSON node "data[0].nested[1].count" should be equal to 0
    And the JSON node "data[0].nested[1].id" should be equal to 2
    And the JSON node "data[0].nested[1].type" should be equal to "filter"
    And the JSON node "data[0].nested[1].title" should be equal to "Filter 2"
    And the JSON node "data[0].nested[1].grouped_by" should be equal to 0

    And the JSON node "data[1].count" should be equal to 0
    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].type" should be equal to "ticket_filter_set"
    And the JSON node "data[1].title" should be equal to "Filter set 2"
    And the JSON node "data[1].grouped_by" should be equal to "filter"
    And the JSON node "data[1].nested" should have 1 element

    And the JSON node "data[1].nested[0].count" should be equal to 0
    And the JSON node "data[1].nested[0].id" should be equal to 3
    And the JSON node "data[1].nested[0].type" should be equal to "filter"
    And the JSON node "data[1].nested[0].title" should be equal to "Filter 3"
    And the JSON node "data[1].nested[0].grouped_by" should be equal to 0

  Scenario: I group by person
    When I send a GET request to "/api/v2/new/ticket_filter_sets/all/counts?group_by[1]=person"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].count" should be equal to 3
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].type" should be equal to "ticket_filter_set"
    And the JSON node "data[0].title" should be equal to "Filter set 1"
    And the JSON node "data[0].grouped_by" should be equal to "filter"
    And the JSON node "data[0].nested" should have 2 elements

    And the JSON node "data[0].nested[0].count" should be equal to 3
    And the JSON node "data[0].nested[0].id" should be equal to 1
    And the JSON node "data[0].nested[0].type" should be equal to "filter"
    And the JSON node "data[0].nested[0].title" should be equal to "Filter 1"
    And the JSON node "data[0].nested[0].grouped_by" should be equal to "person"

    And the JSON node "data[0].nested[0].nested[0].id" should be equal to 3
    And the JSON node "data[0].nested[0].nested[0].type" should be equal to "person"
    And the JSON node "data[0].nested[0].nested[0].title" should be equal to "Ganon User"
    And the JSON node "data[0].nested[0].nested[0].count" should be equal to 3
    And the JSON node "data[0].nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data[0].nested[1].count" should be equal to 0
    And the JSON node "data[0].nested[1].id" should be equal to 2
    And the JSON node "data[0].nested[1].type" should be equal to "filter"
    And the JSON node "data[0].nested[1].title" should be equal to "Filter 2"
    And the JSON node "data[0].nested[1].grouped_by" should be equal to 0

    And the JSON node "data[1].count" should be equal to 0
    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].type" should be equal to "ticket_filter_set"
    And the JSON node "data[1].title" should be equal to "Filter set 2"
    And the JSON node "data[1].grouped_by" should be equal to "filter"
    And the JSON node "data[1].nested" should have 1 element

    And the JSON node "data[1].nested[0].count" should be equal to 0
    And the JSON node "data[1].nested[0].id" should be equal to 3
    And the JSON node "data[1].nested[0].type" should be equal to "filter"
    And the JSON node "data[1].nested[0].title" should be equal to "Filter 3"
    And the JSON node "data[1].nested[0].grouped_by" should be equal to 0
