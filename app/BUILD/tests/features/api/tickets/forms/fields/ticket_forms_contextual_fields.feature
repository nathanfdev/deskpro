@new
Feature: /ticket_forms
  I want to check contextual fields

  Background:
    Given I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
    And only the following Department records exist:
      | #  | Title        | Is Tickets Enabled |
      | d1 | Department 1 | 1                  |
      | d2 | Department 2 | 1                  |

  Scenario: I edit ticket with custom per user fields
    Given only the following custom per user fields exist:
      | #  | Parent | Title    | Context | Type          |
      | f1 |        | Field 1  |         | single_choice |
      | c1 | {f1}   | Choice 1 | {admin} |               |
      | c2 | {f1}   | Choice 2 | {admin} |               |
      | f2 |        | Field 2  |         | multi_choice  |
      | c3 | {f2}   | Choice 3 | {admin} |               |
      | c4 | {f2}   | Choice 4 | {admin} |               |
      | c5 | {f2}   | Choice 5 | {admin} |               |
    And the only default ticket layout exists with fields:
      | agent_layout      |
      | custom_field_{f1} |
      | custom_field_{f2} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": ~admin~,
  "contextual_fields": {
     "~f1~": ~c1~,
     "~f2~": [~c3~,~c5~]
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.contextual_fields" should have 2 elements
    And the JSON node "data.contextual_fields.{f1}.value" should have 1 element
    And the JSON node "data.contextual_fields.{f1}.value[0]" should be equal to "{c1}"
    And the JSON node "data.contextual_fields.{f2}.value" should have 2 elements
    And the JSON node "data.contextual_fields.{f2}.value[0]" should be equal to "{c3}"
    And the JSON node "data.contextual_fields.{f2}.value[1]" should be equal to "{c5}"
