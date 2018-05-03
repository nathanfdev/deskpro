@new
Feature: /currencies endpoint

  Background:
    Given I'm authenticated as admin
    And only the following Currency records exist:
      | #  | Name          | Currency Code | Symbol |
      | c1 | US Dollar     | USD           | $      |
      | c2 | British Pound | GBP           | £      |
      | c2 | Euro          | EUR           | €      |

  Scenario: I retrieve a list of currencies
    When I send a GET request to "/api/v2/currencies"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
