@new
Feature: /ticket_filters endpoint
  To check filter by related entity

  Background:
    Given there are no Person records
    And I'm authenticated as admin
    And only the following Department records exist:
      | #  | Title        | Is Tickets Enabled |
      | d1 | Department 1 | 1                  |
      | d2 | Department 2 | 1                  |
    Given only the following LegacyTicketFilter records exist:
      | #  | Title    | Is Enabled | Is Global | Sys Name | Display Order | Terms                                                                                                                    |
      | f1 | Filter 1 | 1          | 1         | agent    | 1             | [{"type":"status","op":"is","options":{"status":"awaiting_agent"}},{"type":"is_hold","op":"is","options":{"is_hold":0}}] |
    And there are no Ticket records
    And I re-fill ticket search table

  Scenario Outline: I retrieve list of filter's tickets with related entity criteria
    Given the following <entity_type> records exist:
      | #     |
      | ref_1 |
      | ref_2 |
    And only the following Ticket records exist:
      | #        | <property> | Subject  |
      | ticket_1 | {ref_1}    | Ticket 1 |
      | ticket_2 | {ref_1}    | Ticket 2 |
      | ticket_3 | {ref_2}    | Ticket 3 |
      | ticket_4 |            | Ticket 4 |
    And I re-fill ticket search table

    When I send a GET request to "/api/v2/ticket_filters/{f1}/tickets?<property>=~ref_1~"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{ticket_1}"
    And the JSON node "data[0].<property>" should be equal to "{ref_1}"
    And the JSON node "data[1].id" should be equal to "{ticket_2}"
    And the JSON node "data[1].<property>" should be equal to "{ref_1}"

    When I send a GET request to "/api/v2/ticket_filters/{f1}/tickets?<property>=~ref_2~"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{ticket_3}"
    And the JSON node "data[0].<property>" should be equal to "{ref_2}"

  Examples:
    | entity_type  | property     |
    | User         | person       |
    | Agent        | agent        |
    | AgentTeam    | agent_team   |
    | Organization | organization |
    | Department   | department   |
    | Language     | language     |

  Scenario: I retrieve list of filter's tickets with urgency criteria
    Given only the following Ticket records exist:
      | #        | urgency | Subject  |
      | ticket_1 | 2       | Ticket 1 |
      | ticket_2 | 2       | Ticket 2 |
      | ticket_3 | 4       | Ticket 3 |
      | ticket_4 |         | Ticket 4 |
    And I re-fill ticket search table

    When I send a GET request to "/api/v2/ticket_filters/{f1}/tickets?urgency=2"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{ticket_1}"
    And the JSON node "data[0].urgency" should be equal to 2
    And the JSON node "data[1].id" should be equal to "{ticket_2}"
    And the JSON node "data[1].urgency" should be equal to 2

    When I send a GET request to "/api/v2/ticket_filters/{f1}/tickets?urgency=4"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{ticket_3}"
    And the JSON node "data[0].urgency" should be equal to 4

    When I send a GET request to "/api/v2/ticket_filters/{f1}/tickets?urgency=1"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{ticket_4}"
    And the JSON node "data[0].urgency" should be equal to 1

  Scenario: I retrieve list of filter's tickets with additional dates criteria
    When I send a GET request to "/api/v2/ticket_filters/{f1}/tickets?waiting_time=1893456000"
    Then the response status code should be 200
    When I send a GET request to "/api/v2/ticket_filters/{f1}/tickets?all_waiting_time=1893456000"
    Then the response status code should be 200
    When I send a GET request to "/api/v2/ticket_filters/{f1}/tickets?date_created=1893456000"
    Then the response status code should be 200

  Scenario: I retrieve list of filter's tickets with additional custom field criteria
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

    And the object "ticket_1" has "single_choice_field" custom data set to "{single_choice_v1_field},{single_choice_v2_field}"
    And the object "ticket_2" has "single_choice_field" custom data set to "{single_choice_v2_field}"
    And the object "ticket_3" has "single_choice_field" custom data set to "{single_choice_v3_field}"
    And I re-fill ticket search table

    When I send a GET request to "/api/v2/ticket_filters/{f1}/tickets?ticket_field.~single_choice_field~=~single_choice_v1_field~"
    Then the response status code should be 200
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to "{ticket_1}"
    And the JSON node "data[0].fields.{single_choice_field}.value[0]" should be equal to "{single_choice_v1_field}"

    When I send a GET request to "/api/v2/ticket_filters/{f1}/tickets?ticket_field.~single_choice_field~[]={single_choice_v2_field}&ticket_field.~single_choice_field~[]={single_choice_v3_field}&order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{ticket_1}"
    And the JSON node "data[0].fields.{single_choice_field}.value[1]" should be equal to "{single_choice_v2_field}"
    And the JSON node "data[1].id" should be equal to "{ticket_2}"
    And the JSON node "data[1].fields.{single_choice_field}.value[0]" should be equal to "{single_choice_v2_field}"
    And the JSON node "data[2].id" should be equal to "{ticket_3}"
    And the JSON node "data[2].fields.{single_choice_field}.value[0]" should be equal to "{single_choice_v3_field}"
