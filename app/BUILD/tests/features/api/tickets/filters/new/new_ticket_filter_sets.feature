@ticket-filters
Feature: /new/ticket_filter_sets endpoint
  To CRUD DeskPRO tickets
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve list of ticket filter sets w/o sideloading
    When I send a GET request to "/api/v2/new/ticket_filter_sets"
    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].title" should be equal to "Filter set 1"
    And the JSON node "data[0].display_order" should be equal to 10
    And the JSON node "data[0].is_default" should be equal to 1
    And the JSON node "data[0].filters" should exist
    And the JSON node "data[0].private_agent" should exist
    And the JSON node "data[0].shared_agents" should exist

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].title" should be equal to "Filter set 2"
    And the JSON node "data[1].display_order" should be equal to 20
    And the JSON node "data[1].is_default" should be equal to 1

    And the JSON node "data[2].id" should be equal to 3
    And the JSON node "data[2].title" should be equal to "Filter set 3"
    And the JSON node "data[2].display_order" should be equal to 30
    And the JSON node "data[2].is_default" should be equal to 1

    And the JSON node "linked" should have 0 elements


  Scenario: same as above but with sideloading
    When I send a GET request to "/api/v2/new/ticket_filter_sets?include=ticket_filter"
    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "linked.ticket_filter" should exist
    And the JSON node "linked.ticket_filter.1.id" should be equal to 1
    And the JSON node "linked.ticket_filter.1.title" should be equal to "Filter 1"
    And the JSON node "linked.ticket_filter.3.id" should be equal to 3
    And the JSON node "linked.ticket_filter.3.title" should be equal to "Filter 3"

  Scenario: I get ticket filter set w/o sideloading
    When I send a GET request to "/api/v2/new/ticket_filter_sets/1"
    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.title" should be equal to "Filter set 1"
    And the JSON node "data.display_order" should be equal to 10
    And the JSON node "data.is_default" should be equal to 1

    And the JSON node "linked" should have 0 elements

  Scenario: same as above but with sideloading
    When I send a GET request to "/api/v2/new/ticket_filter_sets/1?include=ticket_filter"
    And the response status code should be 200
    And the response should be in JSON

    And the JSON node "linked.ticket_filter" should exist
    And the JSON node "linked.ticket_filter.1.id" should be equal to 1
    And the JSON node "linked.ticket_filter.1.title" should be equal to "Filter 1"
    And the JSON node "linked.ticket_filter.2.id" should be equal to 2
    And the JSON node "linked.ticket_filter.2.title" should be equal to "Filter 2"

  Scenario: I try to create a new filter set with empty request
    When I send a POST request to "/api/v2/new/ticket_filter_sets"
    And the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors.fields.title.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.title.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a new filter set
    When I send a POST request to "/api/v2/new/ticket_filter_sets" with body:
    """
{
  "title": "Filter set 4",
  "display_order": 40,
  "is_default": true
}
    """
    Then the response status code should be 201
    And the response should be in JSON

    And the JSON node "data.id" should be equal to 4
    And the JSON node "data.title" should be equal to "Filter set 4"
    And the JSON node "data.display_order" should be equal to 40
    And the JSON node "data.is_default" should be equal to 1

  Scenario: I modify filter set
    When I send a PUT request to "/api/v2/new/ticket_filter_sets/4" with body:
    """
{
  "title": "Filter set 4 (edited)",
  "display_order": 50,
  "is_default": 0
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/new/ticket_filter_sets/4"
    And the JSON node "data.id" should be equal to 4
    And the JSON node "data.title" should be equal to "Filter set 4 (edited)"
    And the JSON node "data.display_order" should be equal to 50
    And the JSON node "data.is_default" should be equal to 0

  Scenario: I delete filter set
    When I send a DELETE request to "/api/v2/new/ticket_filter_sets/4"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/new/ticket_filter_sets/4"
    Then the response status code should be 404

  Scenario: I get related filters
    When I send a GET request to "/api/v2/new/ticket_filter_sets/1/filters"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].title" should be equal to "Filter 1"
    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].title" should be equal to "Filter 2"

  Scenario: I create filter set with default values
    When I send a POST request to "/api/v2/new/ticket_filter_sets" with body:
    """
{
  "title": "Filter set 5"
}
    """
    Then the response status code should be 201
    And the response should be in JSON

    And the JSON node "data.id" should be equal to 5
    And the JSON node "data.title" should be equal to "Filter set 5"
    And the JSON node "data.display_order" should be equal to 0
    And the JSON node "data.is_default" should be equal to 0
