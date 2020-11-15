@new
Feature:/api/v2/topics/{id}/icon
  To set an icon for topic

  Background:
    Given I'm authenticated as admin
    And only the following Topic records exist:
      | #  | Title        | Slug           | Content              | person          |
      | t1 | Test Article | test-article   | Test Content        | {admin}         |

  Scenario: I check 'application/form-urlencoded' format
    When I send a POST request to "/api/v2/topics/{t1}/icon" with parameters:
      | key           | value                                               |
      | urn           | urn:deskpro:product:icons:fontawesome:fa-bell       |
      | style         | fas                                                 |
      | color         | #00F                                                |
    Then the response status code should be 200
    And the JSON node "data.id" should exist
