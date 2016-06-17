@new
Feature: Widget Chat
  Chat end/reopen

  Background: Fresh database
    Given a user with "user@deskpro.dev" email exists
    And I have guest portal api session with code "AAAAAAAAAAAAAAA"
    And I have authorized portal api session with code "BBBBBBBBBBBBBBB" for "user@deskpro.dev"

  Scenario: I end and reopen chat
    Given only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |

    When I send a POST request to "/portal/api/chats/{chat_1}/end?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/portal/api/chats/{chat_1}/polling?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.ended_by" should be equal to "user"
    And chat property "date_ended" should not be null for chat "{chat_1}"
    When I send a POST request to "/portal/api/chats/{chat_1}/reopen?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/portal/api/chats/{chat_1}/polling?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.date_ended" should be equal to 0
    And the JSON node "chat_info.data.ended_by" should be equal to 0
