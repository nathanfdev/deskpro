@new
Feature:/api/v2/downloads/{id}/icon
  To set an icon for download

  Background:
    Given I'm authenticated as admin
    And only the following Download records exist:
      | #  | Title         | Slug            | Content      |
      | t1 | Test Download | test-download   | Test         |

  Scenario: I check 'application/form-urlencoded' format
    When I send a POST request to "/api/v2/downloads/{t1}/icon" with parameters:
      | key           | value                                               |
      | urn           | urn:deskpro:product:icons:fontawesome:fa-bell       |
      | style         | fas                                                 |
      | color         | #00F                                                |
    Then the response status code should be 200
    And the JSON node "data.id" should exist
