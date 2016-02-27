@ticket-filters
Feature: /ticket_filter_sets endpoint
  To CRUD DeskPRO legacy ticket filter sets
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve list of ticket filter sets
    When I send a GET request to "/api/v2/ticket_filter_sets"
    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].title" should be equal to "Awaiting agent"
    And the JSON node "data[0].display_order" should be equal to 1
    And the JSON node "data[0].is_default" should be equal to 1
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
