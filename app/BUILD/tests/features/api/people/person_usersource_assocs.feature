@new
Feature: Get and delete person usersource associations

  Background:
    Given I'm authenticated as admin
    And only the following Usersource records exist:
      | #  | Title        | Type  | Source Type                           | Is Enabled |
      | u1 | Usersource 1 | agent | deskpro_us_jwt\Usersource\Adapter\Jwt | 1          |
      | u2 | Usersource 2 | agent | deskpro_us_jwt\Usersource\Adapter\Jwt | 1          |
    And only the following UsersourceAssoc records exist:
      | #  | Person  | Identity | Identity Friendly   | Usersource |
      | a1 | {admin} | 1        | email_1@example.com | {u1}       |
      | a2 | {admin} | 2        | email_2@example.com | {u1}       |
      | a3 | {admin} | 3        | email_3@example.com | {u2}       |

  Scenario: I get a usersource
    When I send a GET request to "/api/v2/people/{admin}/usersource_assocs/{a1}"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{a1}"
    And the JSON node "data.usersource" should be equal to "{u1}"
    And the JSON node "data.identity" should be equal to "1"
    And the JSON node "data.identity_friendly" should be equal to "email_1@example.com"
    And the JSON node "data.date_created" should exist
    And the JSON node "data.date_updated" should exist

  Scenario: I get a list of usersources
    When I send a GET request to "/api/v2/people/{admin}/usersource_assocs?order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{a1}"
    And the JSON node "data[0].usersource" should be equal to "{u1}"
    And the JSON node "data[0].identity" should be equal to "1"
    And the JSON node "data[1].id" should be equal to "{a2}"
    And the JSON node "data[2].id" should be equal to "{a3}"

  Scenario: I delete a usersource
    When I send a DELETE request to "/api/v2/people/{admin}/usersource_assocs/{a1}"
    Then the response status code should be 200
