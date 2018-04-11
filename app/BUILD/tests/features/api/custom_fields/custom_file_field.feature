@new
Feature: Custom file field

  Background:
    Given I'm authenticated as admin
    And there are no Blob records in the DB
    And I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    And  I create an image blob with auth code "BBBBBBBBBBBBBBBBBB"

  Scenario: I check single upload
    Given only the following custom person fields exist:
      | #  | Type | Title      | Options                                               |
      | f1 | file | File field | {"multiple": false, "agent_must_extensions": ["txt"]} |
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": [
      "AAAAAAAAAAAAAAAAAA"
    ]
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the JSON node "data.fields.~f1~.value" should have 1 element

  Scenario: I check multiple upload
    Given only the following custom person fields exist:
      | #  | Type | Title      | Options            |
      | f1 | file | File field | {"multiple": true} |
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": [
      "AAAAAAAAAAAAAAAAAA",
      "BBBBBBBBBBBBBBBBBB"
    ]
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the JSON node "data.fields.~f1~.value" should have 2 elements

  Scenario: I check file unset
    Given only the following custom person fields exist:
      | #  | Type | Title      | Options                   |
      | f1 | file | File field | {"agent_required": false} |

    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": []
  }
}
    """
    Then the response status code should be 204


  Scenario: I check file required validation
    Given only the following custom person fields exist:
      | #  | Type | Title      | Options                  |
      | f1 | file | File field | {"agent_required": true} |

    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": []
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~f1~.errors[0].code" should be equal to "required"

  Scenario: I check single upload validation
    Given only the following custom person fields exist:
      | #  | Type | Title      | Options             |
      | f1 | file | File field | {"multiple": false} |

    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": [
      "AAAAAAAAAAAAAAAAAA",
      "BBBBBBBBBBBBBBBBBB"
    ]
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~f1~.errors[0].code" should be equal to "too_many_elements"

  Scenario: I check filesize validation
    Given only the following custom person fields exist:
      | #  | Type | Title      | Options                    |
      | f1 | file | File field | {"agent_max_file_size": 1} |
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": [
      "AAAAAAAAAAAAAAAAAA"
    ]
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~f1~.errors[0].code" should be equal to "accept_size"

  Scenario: I check must extension validation
    Given only the following custom person fields exist:
      | #  | Type | Title      | Options                            |
      | f1 | file | File field | {"agent_must_extensions": ["txt"]} |
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": [
      "BBBBBBBBBBBBBBBBBB"
    ]
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~f1~.errors[0].code" should be equal to "accept_not_in_allowed_exts"

  Scenario: I check not extension validation
    Given only the following custom person fields exist:
      | #  | Type | Title      | Options                           |
      | f1 | file | File field | {"agent_not_extensions": ["jpg"]} |
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": [
      "BBBBBBBBBBBBBBBBBB"
    ]
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~f1~.errors[0].code" should be equal to "accept_not_allowed_exts"

  Scenario: I try to upload a blob using non-existing auth code
    Given only the following custom person fields exist:
      | #  | Type | Title      |
      | f1 | file | File field |
    When I send a PUT request to "/api/v2/people/{admin}" with body:
    """
{
  "fields": {
    "~f1~": [
      "CCCCCCCCCCCCCCCCCC"
    ]
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~f1~.errors[0].code" should be equal to "required"
