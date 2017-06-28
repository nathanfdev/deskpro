@new
Feature: Widget Chat
  Create a new chat

  Background:
    Given no Person records exist
    And I have only default brand
    And a user with "user@deskpro.dev" email exists
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Chat Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1               |
      | d2 | Department 2 | [{defaultBrand}] | 1               |
    And I grant the "{d1}" department permission of chat app for usergroup everyone
    And I grant the "{d2}" department permission of chat app for usergroup everyone

  Scenario: I create a new chat without person info
    When I send a POST request to "/portal/api/chats/create" with parameters:
      | key             | value |
      | chat_department | {d1}  |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.person" should be equal to "0"
    And the JSON node "data.person_name" should be equal to "0"
    And the JSON node "data.person_email" should be equal to "0"
    And the JSON node "data.need_validate_email" should be equal to "0"

  Scenario: I create a new chat with an unknown email
    When I send a POST request to "/portal/api/chats/create" with parameters:
      | key             | value             |
      | email           | unknown@email.com |
      | chat_department | {d1}              |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.person" should exist
    And the JSON node "data.person_name" should be equal to "Unknown"
    And the JSON node "data.person_email" should be equal to "unknown@email.com"
    And the JSON node "data.need_validate_email" should be equal to "0"

  Scenario: I create a new chat with an existing email and different name
    When I send a POST request to "/portal/api/chats/create" with parameters:
      | key             | value            |
      | email           | user@deskpro.dev |
      | name            | New Username     |
      | chat_department | {d1}             |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.person" should exist
    And the JSON node "data.person_name" should be equal to "New Username"
    And the JSON node "data.person_email" should be equal to "user@deskpro.dev"
    And the JSON node "data.need_validate_email" should be equal to "0"
