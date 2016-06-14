@new
Feature: Widget Chat
  Create a new chat

  Background: Fresh database
    Given I have usergroups
    And I have guest portal api session with code "AAAAAAAAAAAAAAA"
    And I have authorized portal api session with code "BBBBBBBBBBBBBBB" for "user@deskpro.dev"

  Scenario: I try to create a new chat without session code
    When I send a POST request to "/portal/api/chats/create"
    Then the response status code should be 403
    And the response should be in JSON
    And the JSON node "message" should be equal to "No token provided"

  Scenario: I create a new chat without person info
    Given the setting "portal.chat.email_validation" is set to 0
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.person" should be equal to "0"
    And the JSON node "data.person_name" should be equal to "0"
    And the JSON node "data.person_email" should be equal to "0"
    And the JSON node "data.need_validate_email" should be equal to "0"

  Scenario: I create a new chat with an unknown email
    Given the setting "portal.chat.email_validation" is set to 0
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key   | value             |
      | email | unknown@email.com |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.person" should exist
    And the JSON node "data.person_name" should be equal to "Unknown"
    And the JSON node "data.person_email" should be equal to "unknown@email.com"
    And the JSON node "data.need_validate_email" should be equal to "0"

  Scenario: I create a new chat with an existing email and different name
    Given the setting "portal.chat.email_validation" is set to 0
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key   | value            |
      | email | user@deskpro.dev |
      | name  | New Username     |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.person" should exist
    And the JSON node "data.person_name" should be equal to "New Username"
    And the JSON node "data.person_email" should be equal to "user@deskpro.dev"
    And the JSON node "data.need_validate_email" should be equal to "0"

  Scenario: I create a new chat without person info but email validation is enabled
    Given the setting "portal.chat.email_validation" is set to 1
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.email.errors[0].code" should be equal to "required"
    And the JSON node "fields.email.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a new chat with email and email validation
    Given the setting "portal.chat.email_validation" is set to 1
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key   | value            |
      | email | user@deskpro.dev |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.person" should exist
    And the JSON node "data.person_name" should be equal to "0"
    And the JSON node "data.person_email" should be equal to "user@deskpro.dev"
    And the JSON node "data.need_validate_email" should be equal to "1"

  Scenario: I create a new chat as guest but require login is enabled
    Given the setting "portal.chat.email_validation" is set to 0
    Given the setting "portal.chat.require_login" is set to 1
    When I send a POST request to "/portal/api/chats/create?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors[0].message" should be equal to "Login required"

  Scenario: I create a new chat as guest and all restrictions are enabled
    Given the setting "portal.chat.email_validation" is set to 1
    Given the setting "portal.chat.require_login" is set to 1
    When I send a POST request to "/portal/api/chats/create?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors[0].message" should be equal to "Login required"
    And the JSON node "fields" should not exist

  Scenario Outline: I create a new chat after login
    Given the setting "portal.chat.email_validation" is set to <email_validation>
    Given the setting "portal.chat.require_login" is set to <require_login>
    When I send a POST request to "/portal/api/chats/create?dpsid={sid_BBBBBBBBBBBBBBB}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.person" should exist
    And the JSON node "data.person_name" should be equal to "Ganon User"
    And the JSON node "data.person_email" should be equal to "user@deskpro.dev"
    And the JSON node "data.need_validate_email" should be equal to "0"

    Examples:
      | email_validation | require_login |
      | 0                | 0             |
      | 0                | 1             |
      | 1                | 0             |
      | 1                | 1             |
