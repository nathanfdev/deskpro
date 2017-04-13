Feature: /new/ticket_filters_counts endpoint
  To ticket filters grouping count
  As an API user
  I want to check endpoint

  Background:
    Given I install the api data set
    And my request is authenticated
    And agent and user exist
    And only the following Language records exist:
      | # | Sys Name |
      | l1 | lang_1  |
      | l2 | lang_2  |
    And only the following Organization records exist:
      | #  | Name  |
      | o1 | Org 1 |
      | o2 | Org 2 |
      | o3 | Org 3 |
    And only the following AgentTeam records exist:
      | #   | Name   |
      | at1 | Team 1 |
      | at2 | Team 2 |
      | at3 | Team 3 |
    And only the following Department records exist:
      | #  | Title |
      | d1 | Dep 1 |
      | d2 | Dep 2 |
    And only the following Ticket records exist:
      | #  | Subject  | Person | Agent   | Language | Organization | Agent Team | Department |
      | t1 | Ticket 1 | {user} | {admin} | {l1}     | {o1}         | {at1}      | {d1}       |
      | t2 | Ticket 2 | {user} | {admin} | {l1}     | {o2}         | {at2}      | {d2}       |
      | t3 | Ticket 3 | {user} | {agent} | NULL     | NULL         | NULL       | {d2}       |

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
    And the JSON node "data.nested[1].nested" should have 0 elements

    And the JSON node "data.nested[2].id" should be equal to 3
    And the JSON node "data.nested[2].title" should be equal to "Filter 3"
    And the JSON node "data.nested[2].type" should be equal to "filter"
    And the JSON node "data.nested[2].count" should be equal to 0
    And the JSON node "data.nested[2].grouped_by" should be equal to 0
    And the JSON node "data.nested[2].nested" should have 0 elements

  Scenario: I group by department
    When I send a GET request to "/api/v2/new/ticket_filters_counts?group_by[1]=department"
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
    And the JSON node "data.nested[0].nested[0].id" should be equal to "{d2}"
    And the JSON node "data.nested[0].nested[0].type" should be equal to "department"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "Dep 2"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements
    And the JSON node "data.nested[0].nested[1].id" should be equal to "{d1}"
    And the JSON node "data.nested[0].nested[1].type" should be equal to "department"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "Dep 1"
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

    And the JSON node "data.nested[0].nested[0].id" should be equal to "{admin}"
    And the JSON node "data.nested[0].nested[0].type" should be equal to "agent"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data.nested[0].nested[1].id" should be equal to "{agent}"
    And the JSON node "data.nested[0].nested[1].type" should be equal to "agent"
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
    And the JSON node "data.nested[0].nested" should have 3 elements

    And the JSON node "data.nested[0].nested[0].type" should be equal to "agent_team"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 1
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data.nested[0].nested[1].type" should be equal to "agent_team"
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
    And the JSON node "data.nested[0].nested" should have 3 elements

    And the JSON node "data.nested[0].nested[0].type" should be equal to "organization"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 1
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data.nested[0].nested[1].type" should be equal to "organization"
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

    And the JSON node "data.nested[0].nested[0].id" should be equal to "{l1}"
    And the JSON node "data.nested[0].nested[0].type" should be equal to "language"
    And the JSON node "data.nested[0].nested[0].title" should be equal to 0
    And the JSON node "data.nested[0].nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    And the JSON node "data.nested[0].nested[1].id" should be equal to 0
    And the JSON node "data.nested[0].nested[1].type" should be equal to "language"
    And the JSON node "data.nested[0].nested[1].title" should be equal to 0
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

  Scenario: I group by waiting time
    When I send a GET request to "/api/v2/new/ticket_filters_counts?group_by[1]=waiting_time"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].title" should be equal to "Filter 1"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to "waiting_time"
    And the JSON node "data.nested[0].nested" should have 1 elements

    And the JSON node "data.nested[0].nested[0].type" should be equal to "waiting_time"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

  Scenario: I group by all waiting time
    When I send a GET request to "/api/v2/new/ticket_filters_counts?group_by[1]=all_waiting_time"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].title" should be equal to "Filter 1"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to "all_waiting_time"
    And the JSON node "data.nested[0].nested" should have 1 elements

    And the JSON node "data.nested[0].nested[0].type" should be equal to "all_waiting_time"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

  Scenario: I group by open time
    When I send a GET request to "/api/v2/new/ticket_filters_counts?group_by[1]=open_time"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].title" should be equal to "Filter 1"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to "open_time"
    And the JSON node "data.nested[0].nested" should have 1 elements

    And the JSON node "data.nested[0].nested[0].type" should be equal to "open_time"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

  Scenario: I group by date created
    When I send a GET request to "/api/v2/new/ticket_filters_counts?group_by[1]=date_created"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].id" should be equal to 1
    And the JSON node "data.nested[0].title" should be equal to "Filter 1"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to "date_created"

    And the JSON node "data.nested[0].nested[0].type" should be equal to "date_created"
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements
