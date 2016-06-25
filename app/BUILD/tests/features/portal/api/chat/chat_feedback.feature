@new
Feature: Widget Chat
  Chat feedback

  Background:
    Given a user with "user@deskpro.dev" email exists
    And I have guest portal api session with code "AAAAAAAAAAAAAAA"

  Scenario: I try to send feedback but chat is not ended yet
    Given only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |

    When I send a POST request to "/portal/api/chats/{chat_1}/feedback?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 400
    And the JSON node "fields.helpful.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "fields.helpful.errors[1].message" should be equal to "Unable to send feedback, chat is not ended yet."

  Scenario: I try to send feedback with no params
    Given only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |

    When I send a POST request to "/portal/api/chats/{chat_1}/end?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 204
    And the response should be empty
    When I send a POST request to "/portal/api/chats/{chat_1}/feedback?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 400
    And the JSON node "fields.helpful.errors[1].message" should not exist

  Scenario: I send positive feedback and reopen chat
    Given only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |
    And I send a POST request to "/portal/api/chats/{chat_1}/end?dpsid={sid_AAAAAAAAAAAAAAA}"

    When I send a POST request to "/portal/api/chats/{chat_1}/feedback?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key     | value |
      | helpful | 10    |
    Then the response status code should be 204
    And the response should be empty
    And chat property "rating_overall" should be equal to 10 for chat "{chat_1}"
    And chat property "rating_comment" should be equal to 0 for chat "{chat_1}"

  Scenario: I send negative feedback
    Given only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |
    And I send a POST request to "/portal/api/chats/{chat_1}/end?dpsid={sid_AAAAAAAAAAAAAAA}"

    When I send a POST request to "/portal/api/chats/{chat_1}/feedback?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key     | value     |
      | helpful | 1         |
      | comment | some text |
    Then the response status code should be 204
    And the response should be empty
    And chat property "rating_overall" should be equal to 1 for chat "{chat_1}"
    And chat property "rating_comment" should be equal to "some text" for chat "{chat_1}"
