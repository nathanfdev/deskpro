Feature: Widget Chat

  Background: Fresh database
    Given I install the fresh data set
    Given I have guest portal api session with code "AAAAAAAAAAAAAAA"
    Given I have authorized portal api session with code "BBBBBBBBBBBBBBB" for "user@deskpro.dev"

  # Create a new chat
  @reinstall
  Scenario: I try to create a new chat without session code
    When I send a POST request to "/portal/api/chats/create"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "message" should be equal to "User session not found"

  Scenario: I create a new chat without person info
    Given the setting "portal.chat.email_validation" is set to 0
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.id" should be equal to "1"
    And the JSON node "data.person" should be equal to "0"
    And the JSON node "data.person_name" should be equal to "0"
    And the JSON node "data.person_email" should be equal to "0"
    And the JSON node "data.need_validate_email" should be equal to "0"

  Scenario: I create a new chat with an unknown email
    Given the setting "portal.chat.email_validation" is set to 0
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key   | value             |
      | email | unknown@email.com |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.id" should be equal to "2"
    And the JSON node "data.person" should be equal to "0"
    And the JSON node "data.person_name" should be equal to "0"
    And the JSON node "data.person_email" should be equal to "unknown@email.com"
    And the JSON node "data.need_validate_email" should be equal to "0"

  Scenario: I create a new chat with an existing email and different name
    Given the setting "portal.chat.email_validation" is set to 0
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key   | value            |
      | email | user@deskpro.dev |
      | name  | New Username     |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.id" should be equal to "3"
    And the JSON node "data.person" should be equal to "4"
    And the JSON node "data.person_name" should be equal to "New Username"
    And the JSON node "data.person_email" should be equal to "user@deskpro.dev"
    And the JSON node "data.need_validate_email" should be equal to "0"

  Scenario: I create a new chat without person info but email validation is enabled
    Given the setting "portal.chat.email_validation" is set to 1
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.email.errors[0].code" should be equal to "required"
    And the JSON node "fields.email.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I create a new chat with email and email validation
    Given the setting "portal.chat.email_validation" is set to 1
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/create?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key   | value            |
      | email | user@deskpro.dev |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.id" should be equal to "4"
    And the JSON node "data.person" should be equal to "4"
    And the JSON node "data.person_name" should be equal to "0"
    And the JSON node "data.person_email" should be equal to "user@deskpro.dev"
    And the JSON node "data.need_validate_email" should be equal to "1"

  Scenario: I create a new chat as guest but require login is enabled
    Given the setting "portal.chat.email_validation" is set to 0
    Given the setting "portal.chat.require_login" is set to 1
    When I send a POST request to "/portal/api/chats/create?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors[0].message" should be equal to "Login required"

  Scenario: I create a new chat as guest and all restrictions is enabled
    Given the setting "portal.chat.email_validation" is set to 1
    Given the setting "portal.chat.require_login" is set to 1
    When I send a POST request to "/portal/api/chats/create?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors[0].message" should be equal to "Login required"
    And the JSON node "fields" should not exist

  Scenario Outline: I create a new chat after login
    Given the setting "portal.chat.email_validation" is set to <email_validation>
    Given the setting "portal.chat.require_login" is set to <require_login>
    When I send a POST request to "/portal/api/chats/create?__sid=2-BBBBBBBBBBBBBBB"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.person" should be equal to "4"
    And the JSON node "data.person_name" should be equal to "Ganon User"
    And the JSON node "data.person_email" should be equal to "user@deskpro.dev"
    And the JSON node "data.need_validate_email" should be equal to "0"

    Examples:
      | email_validation | require_login |
      | 0                | 0             |
      | 0                | 1             |
      | 1                | 0             |
      | 1                | 1             |

  # Chat email validation
  Scenario: I try to validate email without session code
    When I send a POST request to "/portal/api/chats/1/validate/email"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "message" should be equal to "User session not found"

  Scenario: I try to validate email but chat conversation entity has no email (skip check)
    When I send a POST request to "/portal/api/chats/1/validate/email?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key  | value     |
      | code | some code |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.code.errors[0].message" should be equal to "Email should not be validated."

  Scenario: I send empty validation code
    When I send a POST request to "/portal/api/chats/4/validate/email?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.code.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I try to validate email with wrong code
    When I send a POST request to "/portal/api/chats/4/validate/email?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key  | value     |
      | code | some code |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.code.errors[0].message" should be equal to "Wrong email validation code."

  Scenario: I regenerate email validation code
    Given I set chat email validation code "correct code" for chat 4
    When I send a POST request to "/portal/api/chats/4/validate/email/regenerate?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 204
    And the response should be empty
    When I send a POST request to "/portal/api/chats/4/validate/email?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key  | value        |
      | code | correct code |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.code.errors[0].message" should be equal to "Wrong email validation code."

  Scenario: I validate email successfully
    Given I set chat email validation code "correct code" for chat 4
    When I send a POST request to "/portal/api/chats/4/validate/email?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key  | value        |
      | code | correct code |
    Then the response status code should be 204
    And the response should be empty
    When I send a POST request to "/portal/api/chats/4/validate/email?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key  | value        |
      | code | correct code |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.code.errors[0].message" should be equal to "Email is already validated."

  # Chat transcript
  Scenario Outline: I send transcript empty info as guest
    Given the setting "portal.chat.email_validation" is set to <email_validation>
    Given the setting "portal.chat.require_login" is set to <require_login>
    When I send a POST request to "/portal/api/chats/1/transcript/info?__sid=1-AAAAAAAAAAAAAAA"
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
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/1/transcript/info?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key   | value            |
      | email | user@deskpro.dev |
    Then the response status code should be 204
    And the response should be empty

  Scenario: I try to change transcript info (email validation is enabled)
    Given the setting "portal.chat.email_validation" is set to 1
    Given the setting "portal.chat.require_login" is set to 0
    When I send a POST request to "/portal/api/chats/1/transcript/info?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key   | value                    |
      | email | another-user@deskpro.dev |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.email.errors[0].message" should be equal to "Unable to change email, chat email validation is enabled."
    And the JSON node "fields.email.errors[1].message" should not exist
    And the JSON node "fields.name.errors[0].message" should not exist

  Scenario: I try to change transcript info (require login is enabled)
    Given the setting "portal.chat.email_validation" is set to 1
    Given the setting "portal.chat.require_login" is set to 1
    When I send a POST request to "/portal/api/chats/1/transcript/info?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key   | value                    |
      | email | another-user@deskpro.dev |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.email.errors[0].message" should be equal to "Unable to change email, chat require email is enabled."
    And the JSON node "fields.email.errors[1].message" should not exist
    And the JSON node "fields.name.errors[0].message" should not exist

  Scenario Outline: I try to toggle send transcript without email
    Given I reset chat user info for chat 1
    Given the setting "portal.chat.email_validation" is set to <email_validation>
    Given the setting "portal.chat.require_login" is set to <require_login>
    When I send a POST request to "/portal/api/chats/1/transcript/toggle?__sid=1-AAAAAAAAAAAAAAA"
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
    Given I reset chat should send transcript for chat 1
    Given I set chat user "<email>" for chat 1
    When I send a GET request to "/portal/api/chats/1/polling?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.should_send_transcript" should be equal to 0
    When I send a POST request to "/portal/api/chats/1/transcript/toggle?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key                    | value |
      | should_send_transcript | 1     |
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/portal/api/chats/1/polling?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.should_send_transcript" should be equal to 1
    When I send a POST request to "/portal/api/chats/1/transcript/toggle?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/portal/api/chats/1/polling?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.should_send_transcript" should be equal to 0

    Examples:
    | email             |
    | user@deskpro.dev  |
    | unknown@email.com |

  # Chat polling
  Scenario: I'm checking for chat changes
    When I send a GET request to "/portal/api/chats/1/polling?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.id" should exist
    And the JSON node "chat_info.data.person_name" should exist
    And the JSON node "chat_info.data.person_email" should exist
    And the JSON node "chat_info.data.person" should exist
    And the JSON node "chat_info.data.agent" should exist
    And the JSON node "chat_info.data.should_send_transcript" should exist
    And the JSON node "chat_info.data.need_validate_email" should exist
    And the JSON node "chat_info.data.date_created" should exist
    And the JSON node "chat_info.data.date_ended" should exist
    And the JSON node "chat_info.data.ended_by" should exist
    And the JSON node "new_messages.data[0].id" should be equal to 1
    And the JSON node "new_messages.data[0].author_id" should be equal to 0
    And the JSON node "new_messages.data[0].content" should contain "phrase_id"
    And the JSON node "new_messages.data[0].content" should contain "message_started"
    And the JSON node "new_messages.data[0].date_created" should exist
    And the JSON node "new_messages.data[0].is_html" should be equal to 0
    And the JSON node "new_messages.data[0].is_sys" should be equal to 1
    And the JSON node "new_messages.data[0].is_user" should be equal to 0
    And the JSON node "new_messages.data[1].id" should not exist
    When I send a GET request to "/portal/api/chats/1/polling?last_message_id=1&__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.id" should exist
    And the JSON node "new_messages.data[0].id" should not exist

  # Chat end/reopen
  Scenario: I end and reopen chat
    When I send a POST request to "/portal/api/chats/1/end?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/portal/api/chats/1/polling?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.ended_by" should be equal to "user"
    And chat property "date_ended" should not be null for chat 1
    When I send a POST request to "/portal/api/chats/1/reopen?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/portal/api/chats/1/polling?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.date_ended" should be equal to 0
    And the JSON node "chat_info.data.ended_by" should be equal to 0

  # Chat feedback
  Scenario: I try to send feedback but chat is not ended yet
    When I send a POST request to "/portal/api/chats/1/feedback?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 400
    And the JSON node "fields.helpful.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "fields.helpful.errors[1].message" should be equal to "Unable to send feedback, chat is not ended yet."

  Scenario: I try to send feedback with no params
    When I send a POST request to "/portal/api/chats/1/end?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 204
    And the response should be empty
    When I send a POST request to "/portal/api/chats/1/feedback?__sid=1-AAAAAAAAAAAAAAA"
    Then the response status code should be 400
    And the JSON node "fields.helpful.errors[1].message" should not exist

  Scenario: I send positive feedback and reopen chat
    When I send a POST request to "/portal/api/chats/1/feedback?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key     | value |
      | helpful | 10    |
    Then the response status code should be 204
    And the response should be empty
    And chat property "rating_overall" should be equal to 10 for chat 1
    And chat property "rating_comment" should be null for chat 1

  Scenario: I send negative feedback
    When I send a POST request to "/portal/api/chats/1/feedback?__sid=1-AAAAAAAAAAAAAAA" with parameters:
      | key     | value     |
      | helpful | 1         |
      | comment | some text |
    Then the response status code should be 204
    And the response should be empty
    And chat property "rating_overall" should be equal to 1 for chat 1
    And chat property "rating_comment" should be equal to "some text" for chat 1
