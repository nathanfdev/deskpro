@new
Feature: Custom fields
  I want to check choice validation

  Background:
    Given I'm authenticated as admin
    And I have only default brand

  Scenario Outline: I check bad single choice
    Given only the following custom ticket fields exist:
      | #  | Parent | Type   | Title        |
      | t  |        | <type> | Choice field |
      | c1 | {t}    |        | Choice 1     |
      | c2 | {t}    |        | Choice 2     |
      | c3 | {t}    |        | Choice 3     |
    And the only default ticket layout exists with fields:
      | agent_layout     |
      | ticket_field_{t} |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "fields": {
    "~t~": ~t~
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should be equal to "bad_choice"

    Examples:
      | type          |
      | single_choice |
      | radio_group   |

  Scenario Outline: I check bad multiple choice
    Given only the following custom ticket fields exist:
      | #  | Parent | Type   | Title        |
      | t  |        | <type> | Choice field |
      | c1 | {t}    |        | Choice 1     |
      | c2 | {t}    |        | Choice 2     |
      | c3 | {t}    |        | Choice 3     |
    And the only default ticket layout exists with fields:
      | agent_layout     |
      | ticket_field_{t} |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "fields": {
    "~t~": [~t~]
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should be equal to "bad_choice"

    Examples:
      | type           |
      | multi_choice   |
      | checkbox_group |

  Scenario: I check leaf node validation
    Given only the following custom ticket fields exist:
      | #  | Parent | Type          | Title        | Options             |
      | t  |        | single_choice | Choice field |                     |
      | c1 | {t}    |               | Choice 1     |                     |
      | c2 | {t}    |               | Choice 2     | {"parent_id": ~c1~} |
      | c3 | {t}    |               | Choice 3     |                     |
    And the only default ticket layout exists with fields:
      | agent_layout     |
      | ticket_field_{t} |

    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "fields": {
    "~t~": ~c1~
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should be equal to "not_assignable_choice"
