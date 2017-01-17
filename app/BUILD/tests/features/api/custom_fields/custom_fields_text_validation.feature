@new
Feature: Custom fields
  I want to check text/texarea validation

  Background:
    Given I'm authenticated as admin

  Scenario Outline: I check required field
    Given only the following custom ticket fields exist:
      | # | Type   | Title      | Options            |
      | t | <type> | Text field | {"<option>": true} |
    And the only default ticket layout exists with fields:
      | <context>_layout |
      | ticket_field_{t} |

    When I send a POST request to "/api/v2/ticket_forms/<context>" with body:
    """
{
  "fields": {
    "~t~": ""
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should be equal to "required"

    Examples:
      | type     | context | option         |
      | text     | agent   | agent_required |
      | text     | user    | required       |
      | textarea | agent   | agent_required |
      | textarea | user    | required       |

  Scenario Outline: I check min length
    Given only the following custom ticket fields exist:
      | # | Type   | Title      | Options         |
      | t | <type> | Text field | {"<option>": 5} |
    And the only default ticket layout exists with fields:
      | <context>_layout |
      | ticket_field_{t} |

    When I send a POST request to "/api/v2/ticket_forms/<context>" with body:
    """
{
  "fields": {
    "~t~": "123"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should be equal to "length_too_short"

    When I send a POST request to "/api/v2/ticket_forms/<context>" with body:
    """
{
  "fields": {
    "~t~": "12345"
  }
}
    """
    Then the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should not exist

    When I send a POST request to "/api/v2/ticket_forms/<context>" with body:
    """
{
  "fields": {
    "~t~": "1234567"
  }
}
    """
    Then the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should not exist

    Examples:
      | type     | context | option           |
      | text     | agent   | agent_min_length |
      | text     | user    | min_length       |
      | textarea | agent   | agent_min_length |
      | textarea | user    | min_length       |

  Scenario Outline: I check max length
    Given only the following custom ticket fields exist:
      | # | Type   | Title      | Options         |
      | t | <type> | Text field | {"<option>": 5} |
    And the only default ticket layout exists with fields:
      | <context>_layout |
      | ticket_field_{t} |

    When I send a POST request to "/api/v2/ticket_forms/<context>" with body:
    """
{
  "fields": {
    "~t~": "123456"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should be equal to "length_too_long"

    When I send a POST request to "/api/v2/ticket_forms/<context>" with body:
    """
{
  "fields": {
    "~t~": "12345"
  }
}
    """
    Then the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should not exist

    When I send a POST request to "/api/v2/ticket_forms/<context>" with body:
    """
{
  "fields": {
    "~t~": "123"
  }
}
    """
    Then the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should not exist

    Examples:
      | type     | context | option           |
      | text     | agent   | agent_max_length |
      | text     | user    | max_length       |
      | textarea | agent   | agent_max_length |
      | textarea | user    | max_length       |

  Scenario Outline: I check regex validation
    Given only the following custom ticket fields exist:
      | # | Type   | Title      | Options                |
      | t | <type> | Text field | {"<option>": "[0-9]+"} |
    And the only default ticket layout exists with fields:
      | <context>_layout |
      | ticket_field_{t} |

    When I send a POST request to "/api/v2/ticket_forms/<context>" with body:
    """
{
  "fields": {
    "~t~": "abc"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should be equal to "regex"

    When I send a POST request to "/api/v2/ticket_forms/<context>" with body:
    """
{
  "fields": {
    "~t~": "12345"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_{t}.errors[0].code" should not exist

    Examples:
      | type     | context | option      |
      | text     | agent   | agent_regex |
      | text     | user    | regex       |
      | textarea | agent   | agent_regex |
      | textarea | user    | regex       |

  Scenario Outline: I check regex required option
    Given only the following custom ticket fields exist:
      | #  | Type   | Title      | Options                                        |
      | t1 | <type> | Text field | {"<option>": "[0-9]+", "<option>_required": 0} |
      | t2 | <type> | Text field | {"<option>": "[0-9]+", "<option>_required": 1} |
    And the only default ticket layout exists with fields:
      | <context>_layout  |
      | ticket_field_{t1} |
      | ticket_field_{t2} |

    When I send a POST request to "/api/v2/ticket_forms/<context>" with body:
    """
{
  "fields": {
    "~t1~": "",
    "~t2~": ""
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_{t1}.errors[0].code" should not exist
    And the JSON node "errors.fields.fields.fields.fields_{t2}.errors[0].code" should exist

    Examples:
      | type     | context | option      |
      | text     | agent   | agent_regex |
      | text     | user    | regex       |
      | textarea | agent   | agent_regex |
      | textarea | user    | regex       |
