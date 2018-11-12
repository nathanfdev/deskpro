@new
Feature: Custom fields
  I want to check values changes

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |

  Scenario Outline: I check text/textarea/hidden fields
    Given only the following custom ticket fields exist:
      | #  | Type   | Title      |
      | f1 | <type> | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout      |
      | ticket_field_{f1} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": "some value"
  }
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.fields.{f1}.value" should be equal to "some value"

    Examples:
      | type     |
      | text     |
      | textarea |
      | hidden   |

  Scenario Outline: I check toggle field is on
    Given only the following custom ticket fields exist:
      | #  | Type   | Title        |
      | f1 | toggle | Toggle field |
    And the only default ticket layout exists with fields:
      | agent_layout      |
      | ticket_field_{f1} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": <value>
  }
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.fields.{f1}.value" should be equal to 1

    Examples:
      | value |
      | 1     |
      | true  |

  Scenario Outline: I check date field
    Given only the following custom ticket fields exist:
      | #  | Type | Title      |
      | f1 | date | Date field |
    And the only default ticket layout exists with fields:
      | agent_layout      |
      | ticket_field_{f1} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": <request_value>
  }
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.fields.{f1}.value" should be equal to <response_value>

    Examples:
      | request_value         | response_value             |
      | "2016-02-09 17:28:00" | "2016-02-09T00:00:00+0000" |
      | "2016-02-09"          | "2016-02-09T00:00:00+0000" |
      | ""                    | ""                         |

  Scenario Outline: I check datetime field
    Given only the following custom ticket fields exist:
      | #  | Type     | Title          |
      | f1 | datetime | DateTime field |
    And the only default ticket layout exists with fields:
      | agent_layout      |
      | ticket_field_{f1} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": <request_value>
  }
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.fields.{f1}.value" should be equal to <response_value>

    Examples:
      | request_value         | response_value             |
      | "2016-02-09 17:28:00" | "2016-02-09T17:28:00+0000" |
      | ""                    | ""                         |

  Scenario Outline: I check single selectbox/radio group fields
    Given only the following custom ticket fields exist:
      | #  | Parent | Type   | Title        |
      | f1 |        | <type> | Choice field |
      | c1 | {f1}   |        | Choice 1     |
      | c2 | {f1}   |        | Choice 2     |
      | c3 | {f1}   |        | Choice 3     |
    And the only default ticket layout exists with fields:
      | agent_layout      |
      | ticket_field_{f1} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": ~c2~
  }
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.fields.{f1}.value" should have 1 element
    And the JSON node "data.fields.{f1}.value[0]" should be equal to "{c2}"

    Examples:
      | type          |
      | single_choice |
      | radio_group   |

  Scenario Outline: I check set multiple selectbox/checkbox group fields
    Given only the following custom ticket fields exist:
      | #  | Parent | Type   | Title        |
      | f1 |        | <type> | Choice field |
      | c1 | {f1}   |        | Choice 1     |
      | c2 | {f1}   |        | Choice 2     |
      | c3 | {f1}   |        | Choice 3     |
    And the only default ticket layout exists with fields:
      | agent_layout      |
      | ticket_field_{f1} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": [~c1~, ~c2~]
  }
}
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.fields.{f1}.value" should have 2 elements
    And the JSON node "data.fields.{f1}.value[0]" should be equal to "{c1}"
    And the JSON node "data.fields.{f1}.value[1]" should be equal to "{c2}"

    Examples:
      | type           |
      | multi_choice   |
      | checkbox_group |
