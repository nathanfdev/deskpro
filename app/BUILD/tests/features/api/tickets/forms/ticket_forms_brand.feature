@new
Feature: /ticket_forms endpoint
  I want to check possibility to create/modify a ticket with specific brand/department if multi-branding is enabled

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And there are no Department records
    And the only default ticket layout exists with fields:
      | agent_layout |
      | department   |

  Scenario: I have just 1 brand and create a ticket for this brand implicitly
    Given only the following Department records exist:
      | # | Title      | Brands           | Is Tickets Enabled |
      | d | Department | [{defaultBrand}] | 1                  |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
    "subject": "Sample Ticket",
    "message": {
      "message": "<p>my html message</p>",
      "format": "html"
    }
}
    """
    Then the response status code should be 201
    And the JSON node "data.brand" should be equal to "{defaultBrand}"
    And the JSON node "data.department" should be equal to "{d}"

  Scenario: I have just 1 brand and create a ticket or this brand explicitly
    Given only the following Department records exist:
      | # | Title      | Brands           | Is Tickets Enabled |
      | d | Department | [{defaultBrand}] | 1                  |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
    "subject": "Sample Ticket",
    "brand": "~defaultBrand~",
    "department": "~d~",
    "message": {
      "message": "<p>my html message</p>",
      "format": "html"
    }
}
    """
    Then the response status code should be 201
    And the JSON node "data.brand" should be equal to "{defaultBrand}"
    And the JSON node "data.department" should be equal to "{d}"

  Scenario: I try to set department of another brand
    Given I have several brands
    And only the following Department records exist:
      | #  | Title      | Brands           | Is Tickets Enabled |
      | d1 | Department | [{defaultBrand}] | 1                  |
      | d2 | Department | [{otherBrand}]   | 1                  |
      | d3 | Department | [{otherBrand}]   | 1                  |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
    "subject": "Sample Ticket",
    "brand": "~defaultBrand~",
    "department": "~d2~",
    "message": {
      "message": "<p>my html message</p>",
      "format": "html"
    }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.department.errors[0].code" should be equal to the string "bad_choice"

  Scenario Outline: I create a ticket for another brand
    Given I have several brands
    And only the following Department records exist:
      | #  | Title      | Brands           | Is Tickets Enabled |
      | d1 | Department | [{defaultBrand}] | 1                  |
      | d2 | Department | [{otherBrand}]   | 1                  |
      | d3 | Department | [{otherBrand}]   | 1                  |

    When I send a POST request to "/api/v2/ticket_forms/<context>" with body:
    """
{
    "subject": "Sample Ticket",
    "brand": "~otherBrand~",
    "department": "~d2~",
    "message": {
      "message": "<p>my html message</p>",
      "format": "html"
    }
}
    """
    Then the response status code should be 201
    And the JSON node "data.brand" should be equal to "{otherBrand}"
    And the JSON node "data.department" should be equal to "{d2}"

    Examples:
      | context |
      | user    |
      | agent   |
