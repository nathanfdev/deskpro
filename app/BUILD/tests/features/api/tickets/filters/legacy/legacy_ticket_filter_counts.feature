@ticket-filters
Feature: /ticket_filters_counts endpoint
  To legacy ticket filters grouping count
  As a developer
  I want to check endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve list of ticket filters counts
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filters_counts"
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
    And the JSON node "data.nested[1].nested" should have 0 elements

    And the JSON node "data.nested[2].id" should be equal to 3
    And the JSON node "data.nested[2].title" should be equal to "Filter 3"
    And the JSON node "data.nested[2].type" should be equal to "filter"
    And the JSON node "data.nested[2].count" should be equal to 0
    And the JSON node "data.nested[2].grouped_by" should be equal to 0
    And the JSON node "data.nested[2].nested" should have 0 elements

  Scenario: I group by department
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[1]=department"
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
    And the JSON node "data.nested[0].grouped_by" should be equal to "department"
    And the JSON node "data.nested[0].nested" should have 2 elements
    And the JSON node "data.nested[0].nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].nested[0].type" should be equal to "department"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "sales"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements
    And the JSON node "data.nested[0].nested[1].id" should be equal to 2
    And the JSON node "data.nested[0].nested[1].type" should be equal to "department"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "support"
    And the JSON node "data.nested[0].nested[1].count" should be equal to 1
    And the JSON node "data.nested[0].nested[1].nested" should have 0 elements

    And the JSON node "data.nested[1].id" should be equal to 2
    And the JSON node "data.nested[1].title" should be equal to "Filter 2"
    And the JSON node "data.nested[1].type" should be equal to "filter"
    And the JSON node "data.nested[1].count" should be equal to 0
    And the JSON node "data.nested[1].grouped_by" should be equal to 0
    And the JSON node "data.nested[1].nested" should have 0 elements

    And the JSON node "data.nested[2].id" should be equal to 3
    And the JSON node "data.nested[2].title" should be equal to "Filter 3"
    And the JSON node "data.nested[2].type" should be equal to "filter"
    And the JSON node "data.nested[2].count" should be equal to 0
    And the JSON node "data.nested[2].grouped_by" should be equal to 0
    And the JSON node "data.nested[2].nested" should have 0 elements

  Scenario: I group by person
    When I send a GET request to "/api/v2/new/ticket_filters_counts?group_by[1]=person"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].title" should be equal to "Filter 1"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to "person"
    And the JSON node "data.nested[0].nested" should have 1 element

    And the JSON node "data.nested[0].nested[0].id" should be equal to 3
    And the JSON node "data.nested[0].nested[0].type" should be equal to "person"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "Ganon User"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

  Scenario: I group by agent
    When I send a GET request to "/api/v2/new/ticket_filters_counts?group_by[1]=agent"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].title" should be equal to "Filter 1"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to "agent"
    And the JSON node "data.nested[0].nested" should have 2 elements

    And the JSON node "data.nested[0].nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].nested[0].type" should be equal to "agent"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "Link Admin"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data.nested[0].nested[1].id" should be equal to 2
    And the JSON node "data.nested[0].nested[1].type" should be equal to "agent"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "Zelda Agent"
    And the JSON node "data.nested[0].nested[1].count" should be equal to 1
    And the JSON node "data.nested[0].nested[1].nested" should have 0 elements

  Scenario: I group by agent_team
    When I send a GET request to "/api/v2/new/ticket_filters_counts?group_by[1]=agent_team"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].title" should be equal to "Filter 1"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to "agent_team"
    And the JSON node "data.nested[0].nested" should have 2 elements

    And the JSON node "data.nested[0].nested[0].id" should be equal to 0
    And the JSON node "data.nested[0].nested[0].type" should be equal to "agent_team"
    And the JSON node "data.nested[0].nested[0].title" should be equal to 0
    And the JSON node "data.nested[0].nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data.nested[0].nested[1].id" should be equal to 2
    And the JSON node "data.nested[0].nested[1].type" should be equal to "agent_team"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "Support Managers"
    And the JSON node "data.nested[0].nested[1].count" should be equal to 1
    And the JSON node "data.nested[0].nested[1].nested" should have 0 elements

  Scenario: I group by organization
    When I send a GET request to "/api/v2/new/ticket_filters_counts?group_by[1]=organization"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].title" should be equal to "Filter 1"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to "organization"
    And the JSON node "data.nested[0].nested" should have 2 elements

    And the JSON node "data.nested[0].nested[0].id" should be equal to 0
    And the JSON node "data.nested[0].nested[0].type" should be equal to "organization"
    And the JSON node "data.nested[0].nested[0].title" should be equal to 0
    And the JSON node "data.nested[0].nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data.nested[0].nested[1].id" should be equal to 2
    And the JSON node "data.nested[0].nested[1].type" should be equal to "organization"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "Organization 1"
    And the JSON node "data.nested[0].nested[1].count" should be equal to 1
    And the JSON node "data.nested[0].nested[1].nested" should have 0 elements

  Scenario: I group by language
    When I send a GET request to "/api/v2/new/ticket_filters_counts?group_by[1]=language"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].title" should be equal to "Filter 1"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to "language"
    And the JSON node "data.nested[0].nested" should have 2 elements

    And the JSON node "data.nested[0].nested[0].id" should be equal to 0
    And the JSON node "data.nested[0].nested[0].type" should be equal to "language"
    And the JSON node "data.nested[0].nested[0].title" should be equal to 0
    And the JSON node "data.nested[0].nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data.nested[0].nested[1].id" should be equal to 1
    And the JSON node "data.nested[0].nested[1].type" should be equal to "language"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "English"
    And the JSON node "data.nested[0].nested[1].count" should be equal to 1
    And the JSON node "data.nested[0].nested[1].nested" should have 0 elements

  Scenario: I group by urgency
    When I send a GET request to "/api/v2/new/ticket_filters_counts?group_by[1]=urgency"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].title" should be equal to "Filter 1"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to "urgency"
    And the JSON node "data.nested[0].nested" should have 1 elements

    And the JSON node "data.nested[0].nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].nested[0].type" should be equal to "urgency"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "1"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements
