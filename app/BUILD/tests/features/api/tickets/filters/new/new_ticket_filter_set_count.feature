Feature: /new/ticket_filter_sets/{id}/count endpoint
  To ticket filters grouping count
  As an API user
  I want to check endpoint

  Background:
    Given I install the api data set
    And my request is authenticated
    And agent and user exist
    And only the following Ticket records exist:
      | #  | Subject  | Person |
      | t1 | Ticket 1 | {user} |
      | t2 | Ticket 2 | {user} |
      | t3 | Ticket 3 | {user} |

  Scenario: I retrieve list of all ticket filter sets counts
    When I send a GET request to "/api/v2/new/ticket_filter_sets/1/count"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.type" should be equal to "ticket_filter_set"
    And the JSON node "data.title" should be equal to "Filter set 1"
    And the JSON node "data.grouped_by" should be equal to "filter"
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].title" should be equal to "Filter 1"
    And the JSON node "data.nested[0].grouped_by" should be equal to 0

    And the JSON node "data.nested[1].count" should be equal to 0
    And the JSON node "data.nested[1].id" should be equal to 2
    And the JSON node "data.nested[1].type" should be equal to "filter"
    And the JSON node "data.nested[1].title" should be equal to "Filter 2"
    And the JSON node "data.nested[1].grouped_by" should be equal to 0

  Scenario: I group by person
    When I send a GET request to "/api/v2/new/ticket_filter_sets/1/count?group_by[1]=person"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.count" should be equal to 3
    And the JSON node "data.id" should be equal to 1
    And the JSON node "data.type" should be equal to "ticket_filter_set"
    And the JSON node "data.title" should be equal to "Filter set 1"
    And the JSON node "data.grouped_by" should be equal to "filter"
    And the JSON node "data.nested" should have 2 elements

    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].title" should be equal to "Filter 1"
    And the JSON node "data.nested[0].grouped_by" should be equal to "person"

    And the JSON node "data.nested[0].nested[0].id" should be equal to 3
    And the JSON node "data.nested[0].nested[0].type" should be equal to "person"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "Ganon User"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data.nested[1].count" should be equal to 0
    And the JSON node "data.nested[1].id" should be equal to 2
    And the JSON node "data.nested[1].type" should be equal to "filter"
    And the JSON node "data.nested[1].title" should be equal to "Filter 2"
    And the JSON node "data.nested[1].grouped_by" should be equal to 0
