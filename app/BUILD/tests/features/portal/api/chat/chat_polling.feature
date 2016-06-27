@new
Feature: Widget Chat
  Chat polling/send message

  Background:
    Given a user with "user@deskpro.dev" email exists
    And I have guest portal api session with code "AAAAAAAAAAAAAAA"

  Scenario: I'm checking for chat changes
    Given the setting "portal.chat.email_validation" is set to 0
    And the setting "portal.chat.require_login" is set to 0
    And there are no Chat records

    When I send a POST request to "/portal/api/chats/create?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 200

    When I send a GET request to "/portal/api/chats/{lastCreatedId}/polling?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "chat_info.data.id" should be equal to "{lastCreatedId}"
    And the JSON node "chat_info.data.conversation_id" should be equal to "{lastCreatedId}"
    And the JSON node "chat_info.data.person_name" should exist
    And the JSON node "chat_info.data.person_email" should exist
    And the JSON node "chat_info.data.person" should exist
    And the JSON node "chat_info.data.agent" should exist
    And the JSON node "chat_info.data.should_send_transcript" should exist
    And the JSON node "chat_info.data.need_validate_email" should exist
    And the JSON node "chat_info.data.department_id" should exist
    And the JSON node "chat_info.data.department_name" should exist
    And the JSON node "chat_info.data.subject_line" should exist
    And the JSON node "chat_info.data.date_created" should exist
    And the JSON node "chat_info.data.date_ended" should exist
    And the JSON node "chat_info.data.ended_by" should exist
    And the JSON node "new_messages.data[0].author" should be equal to 0
    And the JSON node "new_messages.data[0].content" should contain "phrase_id"
    And the JSON node "new_messages.data[0].content" should contain "message_started"
    And the JSON node "new_messages.data[0].date_created" should exist
    And the JSON node "new_messages.data[0].is_html" should be equal to 0
    And the JSON node "new_messages.data[0].is_sys" should be equal to 1
    And the JSON node "new_messages.data[0].is_user" should be equal to 0
    And the JSON node "new_messages.data[1].id" should not exist

  Scenario: I send empty message
    Given only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |

    When I send a POST request to "/portal/api/chats/{chat_1}/messages?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data[0].id" should not exist

  Scenario: I send text message
    Given only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |

    When I send a POST request to "/portal/api/chats/{chat_1}/messages?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key     | value           |
      | message | my message text |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data[0].content" should contain "my message text"
    And I remember last "{chat_1}" chat message id

    When I send a GET request to "/portal/api/chats/{chat_1}/polling?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.id" should exist
    And the JSON node "new_messages.data[0].content" should contain "my message text"
    And the JSON node "new_messages.data[0].is_sys" should be equal to 0
    And the JSON node "new_messages.data[0].is_user" should be equal to 1

    When I send a GET request to "/portal/api/chats/{chat_1}/polling?last_message_id={lastCreatedId}&dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.id" should exist
    And the JSON node "new_messages.data[0].id" should not exist
