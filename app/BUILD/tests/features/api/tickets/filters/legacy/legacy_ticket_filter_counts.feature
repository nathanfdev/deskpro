Feature: /ticket_filters_counts endpoint
  To legacy ticket filters grouping count
  As an API user
  I want to check endpoint

  Background:
    Given I install the api data set
    And there are no Person records
    And I'm authenticated as admin
    And there are no Ticket records

  Scenario: I retrieve list of ticket filters counts
    Given I have a Ticket record referenced as ticket_1
    And I have a Ticket record referenced as ticket_2
    And I have a Ticket record referenced as ticket_3
    And I re-fill ticket search table

    When I send a GET request to "/api/v2/ticket_filters_counts"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.count" should be equal to 6
    And the JSON node "data.type" should be equal to 0
    And the JSON node "data.title" should be equal to 0
    And the JSON node "data.grouped_by" should be equal to "filter"
    And the JSON node "data.nested" should have 16 elements

    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 3
    And the JSON node "data.nested[0].grouped_by" should be equal to 0
    And the JSON node "data.nested[0].nested" should have 0 elements

    And the JSON node "data.nested[1].title" should be equal to "All (Hold)"
    And the JSON node "data.nested[1].type" should be equal to "filter"
    And the JSON node "data.nested[1].count" should be equal to 0
    And the JSON node "data.nested[1].grouped_by" should be equal to 0
    And the JSON node "data.nested[1].nested" should have 0 elements

    And the JSON node "data.nested[2].title" should be equal to "Archived"
    And the JSON node "data.nested[2].type" should be equal to "filter"
    And the JSON node "data.nested[2].count" should be equal to 0
    And the JSON node "data.nested[2].grouped_by" should be equal to 0
    And the JSON node "data.nested[2].nested" should have 0 elements

    And the JSON node "data.nested[3].title" should be equal to "Awaiting User"
    And the JSON node "data.nested[3].type" should be equal to "filter"
    And the JSON node "data.nested[3].count" should be equal to 0
    And the JSON node "data.nested[3].grouped_by" should be equal to 0
    And the JSON node "data.nested[3].nested" should have 0 elements

  Scenario: I group by user
    Given the following User records exist:
      | #      | Name   |
      | user_1 | User 1 |
      | user_2 | User 2 |
    And only the following Ticket records exist:
      | #        | Person   | Subject  |
      | ticket_1 | {user_1} | Ticket 1 |
      | ticket_2 | {user_1} | Ticket 2 |
      | ticket_3 | {user_2} | Ticket 3 |
      | ticket_4 |          | Ticket 4 |
    And I re-fill ticket search table

    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=person"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 4
    And the JSON node "data.nested[0].grouped_by" should be equal to "person"
    And the JSON node "data.nested[0].nested" should have 2 elements

    And the JSON node "data.nested[0].nested[0].type" should be equal to "person"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "User 1"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 2
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements
    And the JSON node "data.nested[0].nested[1].type" should be equal to "person"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "User 2"
    And the JSON node "data.nested[0].nested[1].count" should be equal to 1
    And the JSON node "data.nested[0].nested[1].nested" should have 0 elements

    And the JSON node "data.nested[1].title" should be equal to "All (Hold)"
    And the JSON node "data.nested[1].type" should be equal to "filter"
    And the JSON node "data.nested[1].count" should be equal to 0
    And the JSON node "data.nested[1].grouped_by" should be equal to 0
    And the JSON node "data.nested[1].nested" should have 0 elements

  Scenario Outline: I group by assignee
    Given only the following <entity_type> records exist:
      | #     | <title_prop>    |
      | ref_1 | <entity_type> 1 |
      | ref_2 | <entity_type> 2 |
    And only the following Ticket records exist:
      | #        | <entity_type> | Subject  |
      | ticket_1 | {ref_1}       | Ticket 1 |
      | ticket_2 | {ref_1}       | Ticket 2 |
      | ticket_3 | {ref_2}       | Ticket 3 |
      | ticket_4 |               | Ticket 4 |
    And I re-fill ticket search table

    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=<group_by>"
    Then the response status code should be 200
    And the response should be in JSON
    And print last JSON response
    And the JSON node "data.nested[0].count" should be equal to 4
    And the JSON node "data.nested[0].grouped_by" should be equal to "<group_by>"
    And the JSON node "data.nested[0].nested" should have 3 elements

    And the JSON node "data.nested[0].nested[0].type" should be equal to "<group_by>"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "<unassigned_label>"
    And the JSON node "data.nested[0].nested[0].count" should be equal to 1
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements
    And the JSON node "data.nested[0].nested[1].type" should be equal to "<group_by>"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "<entity_type> 1"
    And the JSON node "data.nested[0].nested[1].count" should be equal to 2
    And the JSON node "data.nested[0].nested[1].nested" should have 0 elements
    And the JSON node "data.nested[0].nested[2].type" should be equal to "<group_by>"
    And the JSON node "data.nested[0].nested[2].title" should be equal to "<entity_type> 2"
    And the JSON node "data.nested[0].nested[2].count" should be equal to 1
    And the JSON node "data.nested[0].nested[2].nested" should have 0 elements

    Examples:
      | entity_type  | group_by     | unassigned_label | title_prop |
      | Agent        | agent        | Unassigned       | Name       |
      | AgentTeam    | agent_team   | Unassigned       | Name       |
      | Organization | organization | None             | Name       |
      | Department   | department   | None             | Title      |
      | Language     | language     | English          | Title      |

  Scenario: I group by urgency
    Given only the following Ticket records exist:
      | #        | urgency | Subject  |
      | ticket_1 | 2       | Ticket 1 |
      | ticket_2 | 2       | Ticket 2 |
      | ticket_3 | 4       | Ticket 3 |
      | ticket_4 | 0       | Ticket 4 |
    And I re-fill ticket search table

    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=urgency"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].count" should be equal to 4
    And the JSON node "data.nested[0].grouped_by" should be equal to "urgency"
    And the JSON node "data.nested[0].nested" should have 3 elements

    And the JSON node "data.nested[0].nested[0].type" should be equal to "urgency"
    And the JSON node "data.nested[0].nested[0].title" should be equal to 1
    And the JSON node "data.nested[0].nested[0].count" should be equal to 1
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements
    And the JSON node "data.nested[0].nested[1].type" should be equal to "urgency"
    And the JSON node "data.nested[0].nested[1].title" should be equal to 2
    And the JSON node "data.nested[0].nested[1].count" should be equal to 2
    And the JSON node "data.nested[0].nested[1].nested" should have 0 elements
    And the JSON node "data.nested[0].nested[2].type" should be equal to "urgency"
    And the JSON node "data.nested[0].nested[2].title" should be equal to 4
    And the JSON node "data.nested[0].nested[2].count" should be equal to 1
    And the JSON node "data.nested[0].nested[2].nested" should have 0 elements

  Scenario Outline: I group by time
    Given I have a Ticket record referenced as ticket_1
    And I have a Ticket record referenced as ticket_2
    And I have a Ticket record referenced as ticket_3
    And I have a Ticket record referenced as ticket_4
    And I re-fill ticket search table

    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=<group_by>"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].count" should be equal to 4
    And the JSON node "data.nested[0].grouped_by" should be equal to "<group_by>"
    And the JSON node "data.nested[0].nested[0].type" should be equal to "<group_by>"
    And the JSON node "data.nested[0].nested[0].nested" should have 0 elements

    Examples:
      | group_by         |
      | waiting_time     |
      | all_waiting_time |
      | date_created     |

  Scenario: I group by custom fields
    Given only the following CustomDefTicket records exist:
      | #                      | Type          | Title               | Parent                |
      | single_choice_field    | single_choice | Single Choice field |                       |
      | single_choice_v1_field |               | Single Choice v1    | {single_choice_field} |
      | single_choice_v2_field |               | Single Choice v2    | {single_choice_field} |
      | single_choice_v3_field |               | Single Choice v3    | {single_choice_field} |

    And I have a Ticket record referenced as ticket_1
    And I have a Ticket record referenced as ticket_2
    And I have a Ticket record referenced as ticket_3
    And I have a Ticket record referenced as ticket_4

    And the object "ticket_1" has "single_choice_field" custom data with "{single_choice_v1_field},{single_choice_v2_field}"
    And the object "ticket_2" has "single_choice_field" custom data with "{single_choice_v2_field}"
    And the object "ticket_3" has "single_choice_field" custom data with "{single_choice_v3_field}"
    And I re-fill ticket search table

    When I send a GET request to "/api/v2/ticket_filters_counts?group_by[5]=ticket_field.~single_choice_field~"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "data.nested[0].title" should be equal to "All"
    And the JSON node "data.nested[0].type" should be equal to "filter"
    And the JSON node "data.nested[0].count" should be equal to 5
    And the JSON node "data.nested[0].grouped_by" should be equal to "ticket_field.{single_choice_field}"
    And the JSON node "data.nested[0].nested" should have 4 elements

    And the JSON node "data.nested[0].nested[0].count" should be equal to 1
    And the JSON node "data.nested[0].nested[0].type" should be equal to "ticket_field.{single_choice_field}"
    And the JSON node "data.nested[0].nested[0].title" should be equal to "None"
    And the JSON node "data.nested[0].nested[1].count" should be equal to 1
    And the JSON node "data.nested[0].nested[1].type" should be equal to "ticket_field.{single_choice_field}"
    And the JSON node "data.nested[0].nested[1].title" should be equal to "Single Choice v1"
    And the JSON node "data.nested[0].nested[2].count" should be equal to 2
    And the JSON node "data.nested[0].nested[2].type" should be equal to "ticket_field.{single_choice_field}"
    And the JSON node "data.nested[0].nested[2].title" should be equal to "Single Choice v2"
    And the JSON node "data.nested[0].nested[3].count" should be equal to 1
    And the JSON node "data.nested[0].nested[3].type" should be equal to "ticket_field.{single_choice_field}"
    And the JSON node "data.nested[0].nested[3].title" should be equal to "Single Choice v3"
