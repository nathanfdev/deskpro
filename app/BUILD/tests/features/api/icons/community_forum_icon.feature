@new
Feature: /api/v2/community_forums/{id}/icon
  To set an icon for community forum

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And the following CommunityForum records exist:
      | #  | Title      | Description  | brand          |   Noun       |  Plural      | verb_action     |
      | t1 | Test Title | Test         | {defaultBrand} |   Test Title |  Test Titles | Add Test Titles |

  Scenario: I check 'application/form-urlencoded' format
    When I send a POST request to "/api/v2/community_forums/{t1}/icon" with parameters:
      | key           | value                                               |
      | urn           | urn:deskpro:product:icons:fontawesome:fa-bell       |
      | style         | fas                                                 |
      | color         | #00F                                                |
    Then the response status code should be 200
    And the JSON node "data.id" should exist
