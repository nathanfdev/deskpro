@new
Feature: /ticket_layouts endpoint
  I want to check exposing compiled layout JS

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |

  Scenario Outline: I get all layouts JS
    When I send a GET request to "/api/v2/ticket_layouts/<context>.js"
    Then the response status code should be 200
    And the response should contain "getMatchingFields: function (ticket) {"
    And the response should contain "getLayout: function (id) {"

    Examples:
      | context |
      | agent   |
      | user    |

  Scenario Outline: I get department JS
    When I send a GET request to "/api/v2/ticket_layouts/<context>/<department>.js"
    Then the response status code should be 200
    And the response should contain "getMatchingFields: function (ticket) {"
    And the response should contain "getFields: function () {"

    Examples:
      | context | department |
      | agent   | default    |
      | agent   | {d1}       |
      | agent   | {d2}       |
      | user    | default    |
      | user    | {d1}       |
      | user    | {d2}       |
