Feature: /ticket_filter_sets/{id}/count endpoint
  To ticket filters grouping count
  As an API user
  I want to check endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve list of all ticket filter set counts
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filter_sets/1/count"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.count" should be equal to 6
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.type" should be equal to "ticket_filter_set"
    And the JSON node "data.title" should be equal to "Awaiting agent"
    And the JSON node "data.grouped_by" should be equal to "filter"
    And the JSON node "data.nested" should have 10 elements

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to 0
    And the JSON node "data.nested[0].nested" should have 0 elements

    And the JSON node "data.nested[1].id" should be equal to 15
    And the JSON node "data.nested[1].title" should be equal to "All (Hold)"
    And the JSON node "data.nested[1].type" should be equal to "filter"
    And the JSON node "data.nested[1].count" should be equal to 0
    And the JSON node "data.nested[1].grouped_by" should be equal to 0
    And the JSON node "data.nested[1].nested" should have 0 elements

  Scenario: I group by person
    When I send a GET request to "/api/v2/ticket_filter_sets/1/count?group_by[5]=person"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.count" should be equal to 6
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.type" should be equal to "ticket_filter_set"
    And the JSON node "data.title" should be equal to "Awaiting agent"
    And the JSON node "data.grouped_by" should be equal to "filter"
    And the JSON node "data.nested" should have 10 elements

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to "person"
    And the JSON node "data.nested[0].nested" should have 1 elements

    And the JSON node "data.nested[0].nested[0].id" should be equal to 3
    And the JSON node "data.nested[0].nested[0].type" should be equal to "person"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "Ganon User"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements
