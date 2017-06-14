@new
Feature: Widget Chat
  Chat polling/send message

  Background:
    Given a user with "user@deskpro.dev" email exists
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Chat Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1               |
      | d2 | Department 2 | [{defaultBrand}] | 1               |
    And I grant the "{d1}" department permission of chat app for usergroup everyone
    And I grant the "{d2}" department permission of chat app for usergroup everyone
    And only the following Session records exist:
      | #  | Auth            |
      | s1 | AAAAAAAAAAAAAAA |
    And only the following Chat records exist:
      | #  | Person             | Session |
      | c1 | {user@deskpro.dev} | {s1}    |

  Scenario: I'm checking for chat changes
    When I send a GET request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/polling"
    Then the response status code should be 200
    And the response should be in JSON

    And the JSON node "chat_info.data.id" should be equal to "{c1}"
    And the JSON node "chat_info.data.person_name" should exist
    And the JSON node "chat_info.data.person_email" should exist
    And the JSON node "chat_info.data.person" should exist
    And the JSON node "chat_info.data.agent" should exist
    And the JSON node "chat_info.data.should_send_transcript" should exist
    And the JSON node "chat_info.data.need_validate_email" should exist
    And the JSON node "chat_info.data.department" should exist
    And the JSON node "chat_info.data.date_created" should exist
    And the JSON node "chat_info.data.date_ended" should exist
    And the JSON node "chat_info.data.ended_by" should exist

  Scenario: I send empty message
    When I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/messages"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data[0].id" should not exist

  Scenario: I send text message
    When I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/messages" with parameters:
      | key     | value           |
      | message | my message text |
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data[0].content" should contain "my message text"
    And I remember last "{c1}" chat message id

    When I send a GET request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/polling"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.id" should exist
    And the JSON node "new_messages.data[0].content" should contain "my message text"
    And the JSON node "new_messages.data[0].is_sys" should be equal to 0
    And the JSON node "new_messages.data[0].is_user" should be equal to 1

    When I send a GET request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/polling?last_message_id={lastCreatedId}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.id" should exist
    And the JSON node "new_messages.data[0].id" should not exist
