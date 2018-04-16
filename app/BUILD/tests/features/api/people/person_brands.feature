@new
Feature: I check set brands relation on create/edit person

  Background:
    Given no Person records exist
    And no CustomDefPerson records exist
    And I'm authenticated as admin
    And "user@example.com" user exists
    And I have only default brand
    And the following Brand records exist:
      | #  | Name    |
      | b1 | Brand 1 |
      | b2 | Brand 2 |

  Scenario: I create a person with a brand set explicitly
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "Person",
  "primary_email": "email@example.com",
  "brands": [~b1~, ~b2~]
}
    """
    And the response status code should be 201
    And the JSON node "data.brands" should have 2 elements
    And the JSON node "data.brands[0]" should be equal to "{b1}"
    And the JSON node "data.brands[1]" should be equal to "{b2}"

  Scenario: I edit a person with a brand set explicitly
    When I send a PUT request to "/api/v2/people/{user@example.com}" with body:
    """
{
  "name": "Person",
  "primary_email": "email@example.com",
  "brands": [~b2~]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{user@example.com}"
    And the JSON node "data.brands" should have 1 element
    And the JSON node "data.brands[0]" should be equal to "{b2}"

  Scenario: I create a person with a default brand
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "Person",
  "primary_email": "email@example.com"
}
    """
    And the response status code should be 201
    And the JSON node "data.brands" should have 1 element
    And the JSON node "data.brands[0]" should be equal to "{defaultBrand}"

  Scenario: I unset all brands on edit, a default one should left
    When I send a PUT request to "/api/v2/people/{user@example.com}" with body:
    """
{
  "name": "Person",
  "primary_email": "email@example.com",
  "brands": []
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{user@example.com}"
    And the JSON node "data.brands" should have 1 element
    And the JSON node "data.brands[0]" should be equal to "{defaultBrand}"
