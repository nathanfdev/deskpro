@new
Feature: /user_sources endpoint
  To check brands relations

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And the following Brand records exist:
      | #  | Name    |
      | b1 | Brand 1 |
      | b2 | Brand 2 |
    And only the following Usersource records exist:
      | #  | Type  | Title        | Source Type                                    |
      | u1 | agent | Usersource 1 | Application\DeskPRO\Usersource\Adapter\DeskPRO |
      | u2 | user  | Usersource 2 | Application\DeskPRO\Usersource\Adapter\DeskPRO |

  Scenario: I set all brands
    When I send a PUT request to "/api/v2/user_sources/user/{u2}" with body:
    """
{
  "is_all_brands": true
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/user_sources/user/{u2}"
    Then the JSON node "data.is_all_brands" should be equal to 1

  Scenario: I set specific brands
    When I send a PUT request to "/api/v2/user_sources/user/{u2}" with body:
    """
{
  "is_all_brands": false,
  "brands": [~b1~, ~b2~]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/user_sources/user/{u2}"
    Then the JSON node "data.is_all_brands" should be equal to 0
    Then the JSON node "data.brands" should have 2 elements
    Then the JSON node "data.brands[0]" should be equal to "{b1}"
    Then the JSON node "data.brands[1]" should be equal to "{b2}"

  Scenario: I unset brands by setting all brands option
    Given only the following Usersource records exist:
      | #  | Type | Title        | Source Type                                    | Is All Brands | Brands       |
      | u3 | user | Usersource 3 | Application\DeskPRO\Usersource\Adapter\DeskPRO | 1             | [{b1}, {b2}] |

    When I send a PUT request to "/api/v2/user_sources/user/{u3}" with body:
    """
{
  "is_all_brands": true
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/user_sources/user/{u3}"
    Then the JSON node "data.is_all_brands" should be equal to 1
    Then the JSON node "data.brands" should have 0 elements

  Scenario: I'm unable to set specific brands in agent context
    When I send a PUT request to "/api/v2/user_sources/agent/{u1}" with body:
    """
{
  "is_all_brands": true
}
    """
    Then the response status code should be 400
    Then the JSON node "errors.errors[0].code" should be equal to "extra_fields"
