@new
Feature: Widget Chat
  Chat transcript

  Background: Fresh database
    Given a user with "user@deskpro.dev" email exists
    And I have guest portal api session with code "AAAAAAAAAAAAAAA"

  Scenario Outline: I send transcript empty info as guest
    Given the setting "portal.chat.email_validation" is set to <email_validation>
    And the setting "portal.chat.require_login" is set to <require_login>
    And only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |

    When I send a POST request to "/portal/api/chats/{chat_1}/transcript/info?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.email.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "fields.email.errors[1].message" should not exist
    And the JSON node "fields.name.errors[0].message" should not exist

    Examples:
      | email_validation | require_login |
      | 0                | 0             |
      | 0                | 1             |
      | 1                | 0             |
      | 1                | 1             |

  Scenario: I change transcript info as guest (no chat restrictions)
    Given the setting "portal.chat.email_validation" is set to 0
    And the setting "portal.chat.require_login" is set to 0
    And only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |

    When I send a POST request to "/portal/api/chats/{chat_1}/transcript/info?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key   | value            |
      | email | user@deskpro.dev |
    Then the response status code should be 204
    And the response should be empty

  Scenario: I try to change transcript info (email validation is enabled)
    Given the setting "portal.chat.email_validation" is set to 1
    And the setting "portal.chat.require_login" is set to 0
    And only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |

    When I send a POST request to "/portal/api/chats/{chat_1}/transcript/info?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key   | value                    |
      | email | another-user@deskpro.dev |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.email.errors[0].message" should be equal to "Unable to change email, chat email validation is enabled."
    And the JSON node "fields.email.errors[1].message" should not exist
    And the JSON node "fields.name.errors[0].message" should not exist

  Scenario: I try to change transcript info (require login is enabled)
    Given the setting "portal.chat.email_validation" is set to 1
    And the setting "portal.chat.require_login" is set to 1
    And only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |

    When I send a POST request to "/portal/api/chats/{chat_1}/transcript/info?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key   | value                    |
      | email | another-user@deskpro.dev |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.email.errors[0].message" should be equal to "Unable to change email, chat require email is enabled."
    And the JSON node "fields.email.errors[1].message" should not exist
    And the JSON node "fields.name.errors[0].message" should not exist

  Scenario Outline: I try to toggle send transcript without email
    Given the setting "portal.chat.email_validation" is set to <email_validation>
    And the setting "portal.chat.require_login" is set to <require_login>
    And only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |
    And I reset chat user info for chat "{chat_1}"

    When I send a POST request to "/portal/api/chats/{chat_1}/transcript/toggle?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.should_send_transcript.errors[0].message" should be equal to "Person email is not defined."

    Examples:
      | email_validation | require_login |
      | 0                | 0             |
      | 0                | 1             |
      | 1                | 0             |
      | 1                | 1             |

  Scenario Outline: I toggle send transcript
    Given only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |
    And I set chat user "<email>" for chat "{chat_1}"
    And I reset chat should send transcript for chat "{chat_1}"

    When I send a GET request to "/portal/api/chats/{chat_1}/polling?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.should_send_transcript" should be equal to 0
    When I send a POST request to "/portal/api/chats/{chat_1}/transcript/toggle?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key                    | value |
      | should_send_transcript | 1     |
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/portal/api/chats/{chat_1}/polling?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.should_send_transcript" should be equal to 1
    And the JSON node "chat_info.data.conversation_id" should be equal to "{chat_1}"
    When I send a POST request to "/portal/api/chats/{chat_1}/transcript/toggle?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/portal/api/chats/{chat_1}/polling?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.should_send_transcript" should be equal to 0

    Examples:
      | email             |
      | user@deskpro.dev  |
      | unknown@email.com |
