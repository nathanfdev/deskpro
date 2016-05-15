Feature: task counts endpoints
  I want to check task counts

  Background:
    Given I install the "api" data set
    And my request is authenticated

  Scenario: I get group counts
    When I send a GET request to "/api/v2/tasks/counts/groups"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.grouped_by" should be equal to "group"
    And the JSON node "data.count" should be equal to 2

    And the JSON node "data.nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].type" should be equal to "all"

    And the JSON node "data.nested[1].count" should be equal to 1
    And the JSON node "data.nested[1].type" should be equal to "my"

    And the JSON node "data.nested[2].count" should be equal to 0
    And the JSON node "data.nested[2].type" should be equal to "team"

    And the JSON node "data.nested[3].count" should be equal to 0
    And the JSON node "data.nested[3].type" should be equal to "department"

    And the JSON node "data.nested[4].count" should be equal to 0
    And the JSON node "data.nested[4].type" should be equal to "delegated"

    And the JSON node "data.nested[5].count" should be equal to 1
    And the JSON node "data.nested[5].type" should be equal to "unassigned"

  Scenario: I get group counts
    When I send a GET request to "/api/v2/tasks/counts/agents"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.grouped_by" should be equal to "agent"
    And the JSON node "data.count" should be equal to 1

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].count" should be equal to 1
    And the JSON node "data.nested[0].title" should be equal to "Link Admin"

    And the JSON node "data.nested[1].id" should be equal to 2
    And the JSON node "data.nested[1].count" should be equal to 0
    And the JSON node "data.nested[1].title" should be equal to "Zelda Agent"

    And the JSON node "data.nested[2].id" should be equal to 4
    And the JSON node "data.nested[2].count" should be equal to 0
    And the JSON node "data.nested[2].title" should be equal to "Deleted Agent"

  Scenario: I get group counts
    When I send a GET request to "/api/v2/tasks/counts/projects"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.grouped_by" should be equal to "project"
    And the JSON node "data.count" should be equal to 0

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].count" should be equal to 0
    And the JSON node "data.nested[0].title" should be equal to "First project"

    And the JSON node "data.nested[1].id" should be equal to 2
    And the JSON node "data.nested[1].count" should be equal to 0
    And the JSON node "data.nested[1].title" should be equal to "Second project"

    And the JSON node "data.nested[2].id" should be equal to 3
    And the JSON node "data.nested[2].count" should be equal to 0
    And the JSON node "data.nested[2].title" should be equal to "Third project"
