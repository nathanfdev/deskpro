@new
Feature: Widget Chat
  Chat transcript

  Background:
    Given a user with "user@deskpro.dev" email exists
    And only the following Session records exist:
      | #  | Auth            |
      | s1 | AAAAAAAAAAAAAAA |
    And only the following Chat records exist:
      | #  | Person             | Session |
      | c1 | {user@deskpro.dev} | {s1}    |

  Scenario: I change transcript info as guest
    When I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/transcript/info" with parameters:
      | key   | value            |
      | email | user@deskpro.dev |
    Then the response status code should be 204
    And the response should be empty

  Scenario: I try to toggle send transcript without email
    Given I reset chat user info for chat "{c1}"

    When I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/transcript/toggle"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.should_send_transcript.errors[0].message" should be equal to "Person email is not defined."

  Scenario Outline: I toggle send transcript
    And I set chat user "<email>" for chat "{c1}"
    And I reset chat should send transcript for chat "{c1}"

    When I send a GET request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/polling"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.should_send_transcript" should be equal to 0
    When I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/transcript/toggle" with parameters:
      | key                    | value |
      | should_send_transcript | 1     |
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/polling"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.should_send_transcript" should be equal to 1
    And the JSON node "chat_info.data.id" should be equal to "{c1}"
    When I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/transcript/toggle"
    Then the response status code should be 204
    And the response should be empty
    When I send a GET request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/polling"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "chat_info.data.should_send_transcript" should be equal to 0

    Examples:
      | email             |
      | user@deskpro.dev  |
      | unknown@email.com |
