@new
Feature: /ticket_forms validation
  I want to check available layout fields validation

  Background:
    Given I'm authenticated as admin
    And only the following Department records exist:
      | #  | Parent | Title        | Is Tickets Enabled |
      | d1 |        | Department 1 | 1                  |
      | d2 |        | Department 2 | 1                  |
    And there are no TicketLayout records

  Scenario Outline: I check unexpected field on layout
    Given the ticket layout exists for "{d1}" department with fields:
      | agent_layout |
      | cc           |

    When I send a POST request to "/api/v2/ticket_forms/<layout_type>" with body:
    """
{
  "department": <department>,
  "cc": []
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should contain "cc"

    Examples:
      | layout_type | department |
      | agent       | ~d2~       |
      | user        | ~d1~       |
      | user        | ~d2~       |

  Scenario Outline: I check the field is on layout
    Given the only default ticket layout exists with fields:
      | <layout_type>_layout |
      | cc                   |

    When I send a POST request to "/api/v2/ticket_forms/<layout_type>" with body:
    """
{
  "cc": []
}
    """
    Then the JSON node "errors.errors[0].code" should not exist

    Examples:
      | layout_type |
      | agent       |
      | user        |
