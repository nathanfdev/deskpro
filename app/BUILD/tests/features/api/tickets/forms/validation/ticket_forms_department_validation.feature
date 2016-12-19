@new
Feature: /ticket_forms validation
  I want to check department validation

  Background:
    Given I'm authenticated as admin
    And I have only default brand

  Scenario: I try to create a ticket with no subject property in request
    When I send a POST request to "/api/v2/ticket_forms/agent"
    Then the response status code should be 400
    And the JSON node "errors.fields.department.errors[0].code" should be equal to "required"

  Scenario: I sent unknown department choice:
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": 0
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.department.errors[0].code" should be equal to "bad_choice"

  Scenario: I sent not valid data type in choice:
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": {
    "id": 1
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.department.errors[0].code" should be equal to "invalid_data_type"

  Scenario: I try to select leaf department
    Given only the following Department records exist:
      | #  | Parent | Brands           | Title        | Is Tickets Enabled |
      | d1 | NULL   | [{defaultBrand}] | Department 1 | 1                  |
      | d2 | {d1}   | [{defaultBrand}] | Department 2 | 1                  |
      | d3 | {d1}   | [{defaultBrand}] | Department 3 | 1                  |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": ~d1~
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.department.errors" should have 1 element
    And the JSON node "errors.fields.department.errors[0].code" should be equal to "not_assignable_department"

  Scenario: I check layout extra fields
    Given only the following custom ticket fields exist:
      | #   | Type | Title      |
      | ta1 | text | Text field |
      | ta2 | text | Text field |
      | tu1 | text | Text field |
      | tu2 | text | Text field |
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And no TicketLayout records exist
    And the ticket layout exists for "{d1}" department with fields:
      | agent_layout       | user_layout        |
      | ticket_field_{ta1} | ticket_field_{tu1} |
    And the ticket layout exists for "{d2}" department with fields:
      | agent_layout       | user_layout        |
      | ticket_field_{ta2} | ticket_field_{tu2} |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "department": ~d1~,
  "fields": {
    "~ta1~": "text",
    "~ta2~": "text",
    "~tu1~": "text",
    "~tu2~": "text"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.fields.fields.errors[0].message" should not contain "{ta1}"
    And the JSON node "errors.fields.fields.errors[0].message" should contain "{ta2}"
    And the JSON node "errors.fields.fields.errors[0].message" should contain "{tu1}"
    And the JSON node "errors.fields.fields.errors[0].message" should contain "{tu2}"
