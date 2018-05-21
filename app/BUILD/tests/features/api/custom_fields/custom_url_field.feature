@new
Feature: Custom url field

  Background:
    Given I'm authenticated as admin
    And only the following custom person fields exist:
      | #  | Type | Title          | Options                  |
      | f1 | url  | Url field      | {"agent_required": true} |
      | f2 | url  | File url field | {"allow_file": true}     |

  Scenario Outline: I set url
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "<field>": "http://example.com"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the JSON node "data.fields.<field>.value" should be equal to "http://example.com"

    Examples:
      | field |
      | ~f1~  |
      | ~f2~  |

  Scenario: I set file url with file:// protocol
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f2~": "file://example.com"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the JSON node "data.fields.~f2~.value" should be equal to "file://example.com"

  Scenario: I set shared network url
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f2~": "\\\\mynetwork\\myfile"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the JSON node "data.fields.~f2~.value" should be equal to "\\mynetwork\myfile"

  Scenario: I try to set file url with file:// protocol w/o allow_file option
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": "file://example.com"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~f1~.errors[0].code" should be equal to "invalid_url"

  Scenario: I try to set shared network url w/o allow_file option
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": "\\\\mynetwork\\myfile"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~f1~.errors[0].code" should be equal to "invalid_url"

  Scenario Outline: I check url validation
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": "<url>"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~f1~.errors[0].code" should be equal to "invalid_url"

    Examples:
      | url              |
      | http:example.com |
      | example          |

  Scenario: I check required validation
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

  Scenario: I check the url protocol fixer
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": "example.com"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the JSON node "data.fields.~f1~.value" should be equal to "http://example.com"
