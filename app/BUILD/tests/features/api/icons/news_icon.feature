@new
Feature: /api/v2/news/{id}/icon
  To set an icon for news

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following News records exist:
      | #  | Title        | Slug           | Content              | person          | 
      | t1 | Test News   | test-news       | Test Content        | {admin}         |

  Scenario: I check 'application/form-urlencoded' format
    When I send a POST request to "/api/v2/news/{t1}/icon" with parameters:
      | key           | value                                               |
      | urn           | urn:deskpro:product:icons:fontawesome:fa-bell       |
      | style         | fas                                                 |
      | color         | #00F                                                |
    Then the response status code should be 200
    And the JSON node "data.id" should exist
