@new
Feature:/api/v2/guides/{id}/icon
  To set an icon for guide

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following Guide records exist:
      | #  | Title      | Slug         | Description  | brand          |
      | t1 | Test Guide | test-guide   | Test         | {defaultBrand} |

  Scenario: I check 'application/form-urlencoded' format
    When I send a POST request to "/api/v2/guides/{t1}/icon" with parameters:
      | key           | value                                               |
      | urn           | urn:deskpro:product:icons:fontawesome:fa-bell       |
      | style         | fas                                                 |
      | color         | #00F                                                |
    Then the response status code should be 200
    And the JSON node "data.id" should exist
