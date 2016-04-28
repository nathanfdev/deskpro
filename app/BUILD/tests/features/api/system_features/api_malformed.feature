Feature: Api malformed request

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario Outline: I send invalid json request
    When I send a <method> request to "/api/v2/<url>" with body:
    """
<data>
    """
    Then the response status code should be 400
    And the JSON node "status" should be equal to 400
    And the JSON node "code" should be equal to "invalid_json_body"
    And the JSON node "message" should be equal to "The request JSON body is not valid JSON."

  Examples:
    | method | url       | data        |
    | POST   | tickets   | some string |
    | POST   | tickets   | {a: 1}      |
    | POST   | tickets   | {"a": 1,}   |
    | PUT    | tickets/1 | some string |
    | DELETE | tickets/1 | some string |
