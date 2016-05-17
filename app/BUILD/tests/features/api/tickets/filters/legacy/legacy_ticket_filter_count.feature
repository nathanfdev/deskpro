Feature: /ticket_filters/{filter}/count endpoint
  To ticket filters grouping count
  As an API user
  I want to check endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve list of ticket filter count
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filters/5/count"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.id" should be equal to 5
    And the JSON node "data.title" should be equal to "All"
    And the JSON node "data.type" should be equal to "filter"
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.grouped_by" should be equal to 0
    And the JSON node "data.nested" should have 0 elements

  Scenario: I group by person
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filters/5/count?group_by=person"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.id" should be equal to 5
    And the JSON node "data.title" should be equal to "All"
    And the JSON node "data.type" should be equal to "filter"
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.grouped_by" should be equal to "person"
    And the JSON node "data.nested" should have 1 element

    And the JSON node "data.nested[0].id" should be equal to 3
    And the JSON node "data.nested[0].type" should be equal to "person"
    And the JSON node "data.nested[0].title" should be equal to "Ganon User"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested" should have 0 elements
