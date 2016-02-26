Feature: /new/ticket_filters_counts endpoint
  To ticket filters grouping count
  As a developer
  I want to check endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve list of ticket filters counts
    When I send a GET request to "/api/v2/new/ticket_filters_counts"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.id" should be equal to 0
    And the JSON node "data.type" should be equal to 0
    And the JSON node "data.title" should be equal to 0
    And the JSON node "data.grouped_by" should be equal to "filter"

    And the JSON node "data.nested" should have 3 elements
    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].title" should be equal to "Filter 1"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to 0
    And the JSON node "data.nested[0].nested" should have 0 elements

    And the JSON node "data.nested[1].id" should be equal to 2
    And the JSON node "data.nested[1].title" should be equal to "Filter 2"
    And the JSON node "data.nested[1].type" should be equal to "filter"
    And the JSON node "data.nested[1].count" should be equal to 0
    And the JSON node "data.nested[1].grouped_by" should be equal to 0
    And the JSON node "data.nested[0].nested" should have 0 elements

    And the JSON node "data.nested[2].id" should be equal to 3
    And the JSON node "data.nested[2].title" should be equal to "Filter 3"
    And the JSON node "data.nested[2].type" should be equal to "filter"
    And the JSON node "data.nested[2].count" should be equal to 0
    And the JSON node "data.nested[2].grouped_by" should be equal to 0
    And the JSON node "data.nested[0].nested" should have 0 elements
