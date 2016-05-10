@ticket-filters
Feature: /ticket_filters_counts endpoint
  To legacy ticket filters grouping count
  As a developer
  I want to check endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I prepare tickets
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 1",
  "fields": {
    "8": ["10", "11"]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 5

    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 2",
  "fields": {
    "8": ["9"]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 6

    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "Ticket 3",
  "fields": {
    "8": ["10"]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 7

  Scenario: I retrieve list of ticket filters counts
    Given I re-fill ticket search table
    When I send a GET request to "/api/v2/ticket_filters_counts"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.count" should be equal to 12
    And the JSON node "data.id" should be equal to 0
    And the JSON node "data.type" should be equal to 0
    And the JSON node "data.title" should be equal to 0
    And the JSON node "data.grouped_by" should be equal to "filter"
    And the JSON node "data.nested" should have 16 elements

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].grouped_by" should be equal to 0
    And the JSON node "data.nested[0].nested" should have 0 elements

    And the JSON node "data.nested[1].id" should be equal to 15
    And the JSON node "data.nested[1].title" should be equal to "All (Hold)"
    And the JSON node "data.nested[1].type" should be equal to "filter"
    And the JSON node "data.nested[1].count" should be equal to 0
    And the JSON node "data.nested[1].grouped_by" should be equal to 0
    And the JSON node "data.nested[1].nested" should have 0 elements

    And the JSON node "data.nested[2].id" should be equal to 8
    And the JSON node "data.nested[2].title" should be equal to "Archived"
    And the JSON node "data.nested[2].type" should be equal to "filter"
    And the JSON node "data.nested[2].count" should be equal to 0
    And the JSON node "data.nested[2].grouped_by" should be equal to 0
    And the JSON node "data.nested[2].nested" should have 0 elements

    And the JSON node "data.nested[3].id" should be equal to 6
    And the JSON node "data.nested[3].title" should be equal to "Awaiting User"
    And the JSON node "data.nested[3].type" should be equal to "filter"
    And the JSON node "data.nested[3].count" should be equal to 1
    And the JSON node "data.nested[3].grouped_by" should be equal to 0
    And the JSON node "data.nested[3].nested" should have 0 elements

  Scenario: I group by department
    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=department"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.count" should be equal to 12
    And the JSON node "data.id" should be equal to 0
    And the JSON node "data.type" should be equal to 0
    And the JSON node "data.title" should be equal to 0
    And the JSON node "data.grouped_by" should be equal to "filter"
    And the JSON node "data.nested" should have 16 elements

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].grouped_by" should be equal to "department"
    And the JSON node "data.nested[0].nested" should have 2 elements
    And the JSON node "data.nested[0].nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].nested[0].type" should be equal to "department"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "sales"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 5
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements
    And the JSON node "data.nested[0].nested[1].id" should be equal to 2
    And the JSON node "data.nested[0].nested[1].type" should be equal to "department"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "support"
    And the JSON node "data.nested[0].nested[1].count" should be equal to 1
    And the JSON node "data.nested[0].nested[1].nested" should have 0 elements

    And the JSON node "data.nested[1].id" should be equal to 15
    And the JSON node "data.nested[1].title" should be equal to "All (Hold)"
    And the JSON node "data.nested[1].type" should be equal to "filter"
    And the JSON node "data.nested[1].count" should be equal to 0
    And the JSON node "data.nested[1].grouped_by" should be equal to 0
    And the JSON node "data.nested[1].nested" should have 0 elements

    And the JSON node "data.nested[2].id" should be equal to 8
    And the JSON node "data.nested[2].title" should be equal to "Archived"
    And the JSON node "data.nested[2].type" should be equal to "filter"
    And the JSON node "data.nested[2].count" should be equal to 0
    And the JSON node "data.nested[2].grouped_by" should be equal to 0
    And the JSON node "data.nested[2].nested" should have 0 elements

  Scenario: I group by person
    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=person"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].grouped_by" should be equal to "person"
    And the JSON node "data.nested[0].nested" should have 1 element

    And the JSON node "data.nested[0].nested[0].id" should be equal to 3
    And the JSON node "data.nested[0].nested[0].type" should be equal to "person"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "Ganon User"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

  Scenario: I group by agent
    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=agent"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].grouped_by" should be equal to "agent"
    And the JSON node "data.nested[0].nested" should have 3 elements

    And the JSON node "data.nested[0].nested[0].id" should be equal to 0
    And the JSON node "data.nested[0].nested[0].type" should be equal to "agent"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "Unassigned"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data.nested[0].nested[1].id" should be equal to 1
    And the JSON node "data.nested[0].nested[1].type" should be equal to "agent"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "Link Admin"
    And the JSON node "data.nested[0].nested[1].count" should be equal to 2
    And the JSON node "data.nested[0].nested[1].nested" should have 0 elements

    And the JSON node "data.nested[0].nested[2].id" should be equal to 2
    And the JSON node "data.nested[0].nested[2].type" should be equal to "agent"
    And the JSON node "data.nested[0].nested[2].title" should be equal to "Zelda Agent"
    And the JSON node "data.nested[0].nested[2].count" should be equal to 1
    And the JSON node "data.nested[0].nested[2].nested" should have 0 elements

  Scenario: I group by agent_team
    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=agent_team"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].grouped_by" should be equal to "agent_team"
    And the JSON node "data.nested[0].nested" should have 2 elements

    And the JSON node "data.nested[0].nested[0].id" should be equal to 0
    And the JSON node "data.nested[0].nested[0].type" should be equal to "agent_team"
    And the JSON node "data.nested[0].nested[0].title" should be equal to 0
    And the JSON node "data.nested[0].nested[0].count" should be equal to 5
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data.nested[0].nested[1].id" should be equal to 2
    And the JSON node "data.nested[0].nested[1].type" should be equal to "agent_team"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "Support Managers"
    And the JSON node "data.nested[0].nested[1].count" should be equal to 1
    And the JSON node "data.nested[0].nested[1].nested" should have 0 elements

  Scenario: I group by organization
    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=organization"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].grouped_by" should be equal to "organization"
    And the JSON node "data.nested[0].nested" should have 2 elements

    And the JSON node "data.nested[0].nested[0].id" should be equal to 0
    And the JSON node "data.nested[0].nested[0].type" should be equal to "organization"
    And the JSON node "data.nested[0].nested[0].title" should be equal to 0
    And the JSON node "data.nested[0].nested[0].count" should be equal to 5
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data.nested[0].nested[1].id" should be equal to 2
    And the JSON node "data.nested[0].nested[1].type" should be equal to "organization"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "Organization 2"
    And the JSON node "data.nested[0].nested[1].count" should be equal to 1
    And the JSON node "data.nested[0].nested[1].nested" should have 0 elements

  Scenario: I group by language
    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=language"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].grouped_by" should be equal to "language"
    And the JSON node "data.nested[0].nested" should have 1 element

    And the JSON node "data.nested[0].nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].nested[0].type" should be equal to "language"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "English"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

  Scenario: I group by urgency
    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=urgency"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].grouped_by" should be equal to "urgency"
    And the JSON node "data.nested[0].nested" should have 1 elements

    And the JSON node "data.nested[0].nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].nested[0].type" should be equal to "urgency"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "1"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

  Scenario: I group by waiting time
    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=waiting_time"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].grouped_by" should be equal to "waiting_time"

    And the JSON node "data.nested[0].nested[0].type" should be equal to "waiting_time"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

  Scenario: I group by all waiting time
    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=all_waiting_time"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].grouped_by" should be equal to "all_waiting_time"

    And the JSON node "data.nested[0].nested[0].type" should be equal to "all_waiting_time"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

  Scenario: I group by date created
    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=date_created"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].grouped_by" should be equal to "date_created"

    And the JSON node "data.nested[0].nested[0].type" should be equal to "date_created"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

  Scenario: I group by custom fields
    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=ticket_field.5"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].grouped_by" should be equal to "ticket_field.5"
    And the JSON node "data.nested[0].nested" should have 1 element

    And the JSON node "data.nested[0].nested[0].count" should be equal to 6
    And the JSON node "data.nested[0].nested[0].id" should be equal to 0
    And the JSON node "data.nested[0].nested[0].type" should be equal to "ticket_field.5"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "None"

    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=ticket_field.8"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 5
    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 7
    And the JSON node "data.nested[0].grouped_by" should be equal to "ticket_field.8"
    And the JSON node "data.nested[0].nested" should have 4 elements

    And the JSON node "data.nested[0].nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested[0].id" should be equal to 0
    And the JSON node "data.nested[0].nested[0].type" should be equal to "ticket_field.8"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "None"

    And the JSON node "data.nested[0].nested[1].count" should be equal to 1
    And the JSON node "data.nested[0].nested[1].id" should be equal to 9
    And the JSON node "data.nested[0].nested[1].type" should be equal to "ticket_field.8"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "Choice 1"

    And the JSON node "data.nested[0].nested[2].count" should be equal to 2
    And the JSON node "data.nested[0].nested[2].id" should be equal to 10
    And the JSON node "data.nested[0].nested[2].type" should be equal to "ticket_field.8"
    And the JSON node "data.nested[0].nested[2].title" should be equal to "Choice 2"

    And the JSON node "data.nested[0].nested[3].count" should be equal to 1
    And the JSON node "data.nested[0].nested[3].id" should be equal to 11
    And the JSON node "data.nested[0].nested[3].type" should be equal to "ticket_field.8"
    And the JSON node "data.nested[0].nested[3].title" should be equal to "Choice 3"
