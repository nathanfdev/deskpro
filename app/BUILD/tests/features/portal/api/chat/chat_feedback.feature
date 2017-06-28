@new
Feature: Widget Chat
  Chat feedback

  Background:
    Given a user with "user@deskpro.dev" email exists
    And only the following Session records exist:
      | #  | Auth            |
      | s1 | AAAAAAAAAAAAAAA |
    And only the following Chat records exist:
      | #  | Person             | Session |
      | c1 | {user@deskpro.dev} | {s1}    |

  Scenario: I try to send feedback but chat is not ended yet
    When I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/feedback"
    Then the response status code should be 400
    And the JSON node "fields.helpful.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "fields.helpful.errors[1].message" should be equal to "Unable to send feedback, chat is not ended yet."

  Scenario: I try to send feedback with no params
    When I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/end"
    Then the response status code should be 204
    And the response should be empty
    When I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/feedback"
    Then the response status code should be 400
    And the JSON node "fields.helpful.errors[1].message" should not exist

  Scenario: I send positive feedback and reopen chat
    Given I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/end"
    When I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/feedback" with parameters:
      | key     | value |
      | helpful | 10    |
    Then the response status code should be 204
    And the response should be empty
    And chat property "rating_overall" should be equal to 10 for chat "{c1}"
    And chat property "rating_comment" should be equal to 0 for chat "{c1}"

  Scenario: I send negative feedback
    Given I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/end"
    When I send a POST request to "/portal/api/chats/{c1}:AAAAAAAAAAAAAAA/feedback" with parameters:
      | key     | value     |
      | helpful | 1         |
      | comment | some text |
    Then the response status code should be 204
    And the response should be empty
    And chat property "rating_overall" should be equal to 1 for chat "{c1}"
    And chat property "rating_comment" should be equal to "some text" for chat "{c1}"
