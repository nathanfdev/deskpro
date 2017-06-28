@new
Feature: Widget Chat
  Chat end/reopen

  Background:
    Given a user with "user@deskpro.dev" email exists
    And only the following Session records exist:
      | #  | Auth            |
      | s1 | AAAAAAAAAAAAAAA |
    And only the following Chat records exist:
      | #  | Person             | Session |
      | c1 | {user@deskpro.dev} | {s1}    |

  Scenario: I end and reopen chat
    When I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/end"
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/polling"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.ended_by" should be equal to "user"
    And chat property "date_ended" should not be null for chat "{c1}"
    When I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/reopen"
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/polling"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.date_ended" should be equal to 0
    And the JSON node "chat_info.data.ended_by" should be equal to 0
