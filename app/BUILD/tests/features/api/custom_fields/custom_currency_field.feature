@new
Feature: Custom url field

  Background:
    Given I'm authenticated as admin
    And only the following Currency records exist:
      | #  | Name          | Currency Code | Symbol |
      | c1 | US Dollar     | USD           | $      |
      | c2 | British Pound | GBP           | £      |
      | c2 | Euro          | EUR           | €      |
    And only the following custom person fields exist:
      | #  | Type     | Title          | Options                                       |
      | f1 | currency | Currency field | {"agent_required": true, "currency_id": ~c2~} |

  Scenario: I set currency value
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": "10.20"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the JSON node "data.fields.~f1~.value" should be equal to "10.20"

  Scenario: I validate currency value
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": "my val"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~f1~.errors[0].code" should be equal to "numeric"

  Scenario: I validate currency value
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": ""
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~f1~.errors[0].code" should be equal to "required"

  Scenario: I set 0 value
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": "0"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the JSON node "data.fields.~f1~.value" should be equal to "0.00"

  Scenario: I check currency grouping
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": "1,500.20"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the JSON node "data.fields.~f1~.value" should be equal to "1,500.20"
