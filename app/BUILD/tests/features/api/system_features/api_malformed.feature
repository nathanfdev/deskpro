@new
Feature: Api malformed request

  Background:
    Given I'm authenticated as "admin"

  Scenario Outline: I send invalid json request
    When I send a <method> request to "/api/v2/<url>" with body:
    """
<data>
    """
    Then the response status code should be 400
    And the JSON node "status" should be equal to 400
    And the JSON node "code" should be equal to "invalid_input"

    Examples:
      | method | url    | data        |
      | POST   | people | some string |
      | POST   | people | {a: 1}      |
      | POST   | people | {"a": 1,}   |
