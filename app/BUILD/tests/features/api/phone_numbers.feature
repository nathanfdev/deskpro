@new
Feature: I check adding phone numbers to people and orgs

  Background:
    Given no Person records exist
    And no Organization records exist
    And no PersonPhoneNumber records exist
    And no OrganizationPhoneNumber records exist
    And I'm authenticated as admin
    And the following User records exist:
      | #  | Name     |
      | p1 | Person 1 |
      | p2 | Person 2 |
    And the following PersonPhoneNumber records exist:
      | #  | Person | Number    | Region | Guessed Type |
      | n1 | {p1}   | +12345678 | US     | 2            |
    And the following Organization records exist:
      | #  | Name           |
      | o1 | Organization 1 |
      | o2 | Organization 2 |
    And the following OrganizationPhoneNumber records exist:
      | #  | Organization | Number    | Region | Guessed Type |
      | n2 | {o1}         | +23456789 | US     | 2            |

  Scenario Outline: I check phone numbers field
    When I send a GET request to "/api/v2/<endpoint>/<ref>"
    Then the response status code should be 200
    And the JSON node "data.phone_numbers" should have 1 element
    And the JSON node "data.phone_numbers[0].number" should be equal to "<expected_number>"

    Examples:
      | endpoint      | ref  | expected_number |
      | people        | {p1} | +12345678       |
      | organizations | {o1} | +23456789       |

  Scenario Outline: I set phone numbers
    When I send a PUT request to "/api/v2/<endpoint>/<ref>" with body:
    """
{
  "phone_numbers": [
    {
      "number": "+12345345345"
    },
    {
      "number": "+12345345346"
    }
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/<endpoint>/<ref>"
    Then the JSON node "data.phone_numbers" should have 2 elements
    And the JSON node "data.phone_numbers[0].number" should be equal to "+12345345345"
    And the JSON node "data.phone_numbers[1].number" should be equal to "+12345345346"

    Examples:
      | endpoint      | ref  |
      | people        | {p1} |
      | organizations | {o1} |

  Scenario Outline: I unset phone numbers
    When I send a PUT request to "/api/v2/<endpoint>/<ref>" with body:
    """
{
  "phone_numbers": []
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/<endpoint>/<ref>"
    Then the JSON node "data.phone_numbers" should have 0 elements

    Examples:
      | endpoint      | ref  |
      | people        | {p1} |
      | organizations | {o1} |

  Scenario Outline: I try to set an invalid phone number
    When I send a PUT request to "/api/v2/<endpoint>/<ref>" with body:
    """
{
  "phone_numbers": [
    {
      "number": "number"
    }
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.phone_numbers.fields.phone_numbers_0.errors[0].code" should be equal to "invalid_phone_number_format"

    Examples:
      | endpoint      | ref  |
      | people        | {p1} |
      | organizations | {o1} |
