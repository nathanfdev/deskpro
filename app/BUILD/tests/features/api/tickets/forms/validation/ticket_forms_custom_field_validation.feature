@new
Feature: /ticket_forms validation
  I want to check built in fields validation

  Background:
    Given I'm authenticated as admin

  Scenario: I sent not valid data for custom data
    Given only the following custom ticket fields exist:
      | #          | Type | Title      |
      | text_field | text | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout              |
      | ticket_field_{text_field} |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "fields": "some data"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.errors[0].code" should be equal to "invalid_data_type"

  Scenario: I check ticket custom field validation
    Given only the following custom ticket fields exist:
      | #          | Type | Title      | Options                 |
      | text_field | text | Text field | {"agent_min_length": 5} |
    And the only default ticket layout exists with fields:
      | agent_layout              |
      | ticket_field_{text_field} |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "fields": {
    "~text_field~": "abc"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_{text_field}.errors[0].code" should be equal to "length_too_short"

  Scenario: I check person custom field validation
    Given only the following custom person fields exist:
      | #          | Type | Title      | Options                 |
      | text_field | text | Text field | {"agent_min_length": 5} |
    And the only default ticket layout exists with fields:
      | agent_layout            |
      | user_field_{text_field} |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "user_fields": {
    "~text_field~": "abc"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.user_fields.fields.user_fields_{text_field}.errors[0].code" should be equal to "length_too_short"

  Scenario: I check org custom field validation
    Given only the following custom organization fields exist:
      | #          | Type | Title      | Options                 |
      | text_field | text | Text field | {"agent_min_length": 5} |
    And the only default ticket layout exists with fields:
      | agent_layout           |
      | org_field_{text_field} |
    And only the following Organization records exist:
      | #    | Name  |
      | org1 | Org 1 |
    And the following User records exist:
      | #  | Organization |
      | p1 | {org1}       |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": ~p1~,
  "organization_fields": {
    "~text_field~": "abc"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.organization_fields.fields.organization_fields_{text_field}.errors[0].code" should be equal to "length_too_short"

  Scenario: I check that org field was not added because person has no organization
    Given only the following custom organization fields exist:
      | #          | Type | Title      |
      | text_field | text | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout           |
      | org_field_{text_field} |
    And the following User records exist:
      | #  | Organization |
      | p1 | NULL         |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "person": ~p1~,
  "organization_fields": {
    "~text_field~": "abc"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should contain "organization_fields"

  Scenario: I check person and ticket has different organizations
    Given only the following custom organization fields exist:
      | #          | Type | Title      | Options                 |
      | text_field | text | Text field | {"agent_min_length": 5} |
    And the only default ticket layout exists with fields:
      | agent_layout           |
      | org_field_{text_field} |
    And only the following Organization records exist:
      | #    | Name  |
      | org1 | Org 1 |
      | org2 | Org 2 |
    And the following User records exist:
      | #  | Organization |
      | p1 | {org1}       |
    And the following Ticket records exist:
      | #  | Subject  | Organization |
      | t1 | Ticket 1 | {org1}       |
      | t2 | Ticket 2 | {org2}       |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": ~p1~,
  "organization_fields": {
    "~text_field~": "abc"
  }
}
    """
    Then the JSON node "errors.errors" should not exist

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t2}" with body:
    """
{
  "person": ~p1~,
  "organization_fields": {
    "~text_field~": "abc"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.errors[0].message" should contain "organization_fields"
