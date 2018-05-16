@new
Feature: I check different request format support

  Background:
    Given there are no "Person" records
    And no CustomDefPerson records exist
    And I'm authenticated as "admin"

  Scenario: I check 'application/json' format
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "Person Name",
  "primary_email": "email@example.com"
}
    """
    Then the response status code should be 201

  Scenario: I check 'application/form-urlencoded' format
    When I send a POST request to "/api/v2/people" with parameters:
      | key           | value             |
      | name          | Person Name       |
      | primary_email | email@example.com |
    Then the response status code should be 201
