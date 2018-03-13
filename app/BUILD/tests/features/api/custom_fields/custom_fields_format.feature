@new @radu
Feature: Custom fields
  I want to check custom fields request data format

  Background:
    Given no Person records exist
    And I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |

  Scenario: I check inline format
    Given only the following custom ticket fields exist:
      | #  | Type | Title      |
      | f1 | text | Text field |
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

  Scenario: I check output format
    Given only the following custom ticket fields exist:
      | #  | Type | Title      |
      | f1 | text | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout      |
      | ticket_field_{f1} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": {
      "value": "some value"
    }
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.fields.{f1}.value" should be equal to "some value"

  Scenario: I check output format for multi choice field
    Given only the following custom ticket fields exist:
      | #  | Parent | Type         | Title        |
      | f1 |        | multi_choice | Choice field |
      | c1 | {f1}   |              | Choice 1     |
      | c2 | {f1}   |              | Choice 2     |
      | c3 | {f1}   |              | Choice 3     |
    And the only default ticket layout exists with fields:
      | agent_layout      |
      | ticket_field_{f1} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": {
      "value": ["~c1~", "~c2~"],
      "detail": {
        "~c1~": {"id": "~c1~", "title": "Choice 1"},
        "~c2~": {"id": "~c2~", "title": "Choice 2"}
      }
    }
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the JSON node "data.fields.{f1}.value[0]" should be equal to "{c1}"
    Then the JSON node "data.fields.{f1}.value[1]" should be equal to "{c2}"

  Scenario Outline: I check text/textarea/hidden response format
    Given only the following custom person fields exist:
      | #  | Type   | Title        |
      | f1 | <type> | Custom field |
    And the object "admin" has "f1" custom data set to "some value"

    When I send a GET request to "/api/v2/people/{admin}"
    Then the response status code should be 200
    And the JSON node "data.fields" should have 1 element
    And the JSON node "data.fields.{f1}.aliases" should be equal to node:
      """
      []
      """
    And the JSON node "data.fields.{f1}.value" should be equal to the string "some value"

    Examples:
      | type     |
      | text     |
      | textarea |
      | hidden   |

  Scenario Outline: I check date/datetime response format
    Given only the following custom person fields exist:
      | #  | Type   | Title        |
      | f1 | <type> | Custom field |
    And the object "admin" has "f1" custom data set to "2016-07-08 18:00"

    When I send a GET request to "/api/v2/people/{admin}"
    Then the response status code should be 200
    And the JSON node "data.fields.{f1}.aliases" should be equal to node:
      """
      []
      """
    And the JSON node "data.fields.{f1}.value" should be equal to the string "<value>"

    Examples:
      | type     | value                    |
      | date     | 2016-07-08T00:00:00+0000 |
      | datetime | 2016-07-08T18:00:00+0000 |

  Scenario Outline: I check toggle response format
    Given only the following custom person fields exist:
      | #  | Type   | Title        |
      | f1 | toggle | Custom field |
    And the object "admin" has "f1" custom data set to "<value>"

    When I send a GET request to "/api/v2/people/{admin}"
    Then the response status code should be 200
    And the JSON node "data.fields" should have 1 element
    And the JSON node "data.fields.{f1}.aliases" should be equal to node:
      """
      []
      """
    And the JSON node "data.fields.{f1}.value" should be equal to "<value>"

    Examples:
      | value |
      | 0     |
      | 1     |

  Scenario Outline: I check single choice field format
    Given only the following custom person fields exist:
      | #  | Parent | Type   | Title        |
      | f1 |        | <type> | Custom field |
      | c1 | {f1}   | <type> | Choice 1     |
      | c2 | {f1}   | <type> | Choice 2     |
    And the object "admin" has "f1" custom data set to "{c1}"

    When I send a GET request to "/api/v2/people/{admin}"
    Then the response status code should be 200
    And the JSON node "data.fields" should have 1 element
    And the JSON node "data.fields.{f1}.aliases" should be equal to node:
      """
      []
      """
    And the JSON node "data.fields.{f1}.value" should exist
    And the JSON node "data.fields.{f1}.value" should have 1 element
    And the JSON node "data.fields.{f1}.value[0]" should be equal to "{c1}"
    And the JSON node "data.fields.{f1}.detail" should exist
    And the JSON node "data.fields.{f1}.detail" should have 1 element
    And the JSON node "data.fields.{f1}.detail.{c1}" should exist
    And the JSON node "data.fields.{f1}.detail.{c1}" should have 2 elements
    And the JSON node "data.fields.{f1}.detail.{c1}.id" should be equal to "{c1}"
    And the JSON node "data.fields.{f1}.detail.{c1}.title" should be equal to "Choice 1"

    Examples:
      | type          |
      | single_choice |
      | radio_group   |

  Scenario Outline: I check multi choice field format
    Given only the following custom person fields exist:
      | #  | Parent | Type   | Title        |
      | f1 |        | <type> | Custom field |
      | c1 | {f1}   | <type> | Choice 1     |
      | c2 | {f1}   | <type> | Choice 2     |
    And the object "admin" has "f1" custom data set to "{c1},{c2}"

    When I send a GET request to "/api/v2/people/{admin}"
    Then the response status code should be 200
    Then print last JSON response
    And the JSON node "data.fields" should have 1 element
    And the JSON node "data.fields.{f1}.aliases" should be equal to node:
      """
      []
      """
    And the JSON node "data.fields.{f1}.value" should exist
    And the JSON node "data.fields.{f1}.value" should have 2 elements
    And the JSON node "data.fields.{f1}.value[0]" should be equal to "{c1}"
    And the JSON node "data.fields.{f1}.value[1]" should be equal to "{c2}"
    And the JSON node "data.fields.{f1}.detail" should exist
    And the JSON node "data.fields.{f1}.detail" should have 2 elements
    And the JSON node "data.fields.{f1}.detail.{c1}" should exist
    And the JSON node "data.fields.{f1}.detail.{c1}" should have 2 elements
    And the JSON node "data.fields.{f1}.detail.{c1}.id" should be equal to "{c1}"
    And the JSON node "data.fields.{f1}.detail.{c1}.title" should be equal to "Choice 1"
    And the JSON node "data.fields.{f1}.detail.{c2}" should exist
    And the JSON node "data.fields.{f1}.detail.{c2}" should have 2 elements
    And the JSON node "data.fields.{f1}.detail.{c2}.id" should be equal to "{c2}"
    And the JSON node "data.fields.{f1}.detail.{c2}.title" should be equal to "Choice 2"

    Examples:
      | type           |
      | multi_choice   |
      | checkbox_group |
