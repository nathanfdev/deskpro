@new
Feature: /user_sources endpoint
  Get usersource by app id

  Background:
    Given I'm authenticated as admin
    And only the following AppPackage records exist:
      | #  | Name          | Title         | Description   | Author Name | Author Email     | Author Link        | Api Version | Version | Version Name | Tags |
      | p1 | App Package 1 | App Package 1 | App Package 1 | Deskpro     | user@example.com | http://example.com | 1.0.0       | 2       | 2            | ["tag1", "tag2"]     |
      | p2 | App Package 2 | App Package 2 | App Package 2 | Deskpro     | user@example.com | http://example.com | 1.0.0       | 2       | 2            |   ["tag1", "tag2"]   |
    And only the following AppInstance records exist:
      | #  | Package | Title          |
      | i1 | {p1}    | App Instance 1 |
      | i2 | {p2}    | App Instance 2 |
    And only the following Usersource records exist:
      | #  | Type  | Title        | Source Type                                    | App  |
      | u1 | agent | Usersource 1 | Application\DeskPRO\Usersource\Adapter\DeskPRO | {i1} |
      | u2 | user  | Usersource 2 | Application\DeskPRO\Usersource\Adapter\DeskPRO | {i2} |
      | u3 | user  | Usersource 2 | Application\DeskPRO\Usersource\Adapter\DeskPRO | NULL |

  Scenario: I get a usersource by app instance id
    When I send a GET request to "/api/v2/user_sources/user/app-{i2}"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{u2}"

  Scenario: I can't get a usersource by app instance id with wrong context
    When I send a GET request to "/api/v2/user_sources/user/app-{i1}"
    Then the response status code should be 404

  Scenario: I get local usersource
    When I send a GET request to "/api/v2/user_sources/user/deskpro"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{u3}"
