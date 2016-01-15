Feature: Widget Chat

  Background: Fresh database
    Given I install the fresh data set
    Given I have guest portal api session with code "BKNPKHB2A9N9SA8"

  # Chat create
  Scenario: I try to create a new chat without session code
    When I send a POST request to "/portal/api/chats/create"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "message" should be equal to "User session not found"

  Scenario: I create a new chat without person info
    Given the setting "portal.chat.email_validation" is set to 0
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?__sid=1-BKNPKHB2A9N9SA8"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.id" should be equal to "1"
    And the JSON node "data.person" should be equal to "0"
    And the JSON node "data.person_name" should be equal to "0"
    And the JSON node "data.person_email" should be equal to "0"

  Scenario: I create a new chat with an unknown email
    Given the setting "portal.chat.email_validation" is set to 0
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?__sid=1-BKNPKHB2A9N9SA8" with parameters:
      | key   | value             |
      | email | unknown@email.com |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.id" should be equal to "2"
    And the JSON node "data.person" should be equal to "0"
    And the JSON node "data.person_name" should be equal to "0"
    And the JSON node "data.person_email" should be equal to "unknown@email.com"

  Scenario: I create a new chat with an existing email
    Given the setting "portal.chat.email_validation" is set to 0
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?__sid=1-BKNPKHB2A9N9SA8" with parameters:
      | key   | value            |
      | email | user@deskpro.dev |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.id" should be equal to "3"
    And the JSON node "data.person" should be equal to "4"
    And the JSON node "data.person_name" should be equal to "0"
    And the JSON node "data.person_email" should be equal to "user@deskpro.dev"

  Scenario: I create a new chat without person info but email validation is enabled
    Given the setting "portal.chat.email_validation" is set to 1
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?__sid=1-BKNPKHB2A9N9SA8"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.email.errors[0].code" should be equal to "required"
    And the JSON node "fields.email.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a new chat as guest but require login is enabled
    Given the setting "portal.chat.email_validation" is set to 0
    Given the setting "portal.chat.require_login" is set to 1
    When I send a POST request to "/portal/api/chats/create?__sid=1-BKNPKHB2A9N9SA8"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors[0].message" should be equal to "Login required"

  Scenario: I create a new chat as guest and all restrictions is enabled
    Given the setting "portal.chat.email_validation" is set to 1
    Given the setting "portal.chat.require_login" is set to 1
    When I send a POST request to "/portal/api/chats/create?__sid=1-BKNPKHB2A9N9SA8"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors[0].message" should be equal to "Login required"
    And the JSON node "fields" should not exist
