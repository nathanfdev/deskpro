@new
Feature: /ticket_forms endpoint
  I want to check skipped department field if there is just one selectable department

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And there are no Department records
    And the only default ticket layout exists with fields:
      | agent_layout |
      | department   |

  Scenario: I have just one department
    Given only the following Department records exist:
      | # | Title      | Brands           | Is Tickets Enabled |
      | d | Department | [{defaultBrand}] | 1                  |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
    "subject": "Sample Ticket",
    "department": ~d~,
    "message": {
      "message": "<p>my html message</p>",
      "format": "html"
    }
}
    """
    Then the response status code should be 201
    And the JSON node "data.department" should be equal to "{d}"

  Scenario: I have just one department and skip it in request params
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
    And the JSON node "data.department" should be equal to "{d}"

  Scenario: I have just one selectable department
    Given only the following Department records exist:
      | #  | Parent | Brands           | Title          | Is Tickets Enabled |
      | d1 |        | [{defaultBrand}] | Department 1   | 1                  |
      | d2 | {d1}   | [{defaultBrand}] | Department 1a  | 1                  |
      | d3 | {d2}   | [{defaultBrand}] | Department 1aa | 1                  |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
    "subject": "Sample Ticket",
    "department": ~d3~,
    "message": {
      "message": "<p>my html message</p>",
      "format": "html"
    }
}
    """
    Then the response status code should be 201
    And the JSON node "data.department" should be equal to "{d3}"

  Scenario: I have just one selectable department and skip it in request params
    Given only the following Department records exist:
      | #  | Parent | Brands           | Title          | Is Tickets Enabled |
      | d1 |        | [{defaultBrand}] | Department 1   | 1                  |
      | d2 | {d1}   | [{defaultBrand}] | Department 1a  | 1                  |
      | d3 | {d2}   | [{defaultBrand}] | Department 1aa | 1                  |

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
    And the JSON node "data.department" should be equal to "{d3}"

  Scenario: I have just one selectable department but try to set an another one
    Given only the following Department records exist:
      | #  | Title        |  Brands          | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 0                  |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
    "department": ~d2~
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.department.errors[0].code" should be equal to the string "bad_choice"

  Scenario: I have more than one selectable department
    Given only the following Department records exist:
      | #  | Parent | Brands           | Title          | Is Tickets Enabled |
      | d1 |        | [{defaultBrand}] | Department 1   | 1                  |
      | d2 | {d1}   | [{defaultBrand}] | Department 1a  | 1                  |
      | d3 | {d1}   | [{defaultBrand}] | Department 1b  | 1                  |
      | d4 | {d2}   | [{defaultBrand}] | Department 1aa | 1                  |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
    "subject": "Sample Ticket",
    "department": ~d4~,
    "message": {
      "message": "<p>my html message</p>",
      "format": "html"
    }
}
    """
    Then the response status code should be 201
    And the JSON node "data.department" should be equal to "{d4}"
