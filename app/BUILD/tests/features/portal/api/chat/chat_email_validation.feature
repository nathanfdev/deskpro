@new
Feature: Widget Chat
  Chat email validation

  Background:
    Given a user with "user@deskpro.dev" email exists
    And I have guest portal api session with code "AAAAAAAAAAAAAAA"

  Scenario: I try to validate email without session code
    Given only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |

    When I send a POST request to "/portal/api/chats/{chat_1}/validate/email"
    Then the response status code should be 403
    And the response should be in JSON
    And the JSON node "message" should be equal to "No token provided"

  Scenario: I try to validate email but chat conversation entity has no email (skip check)
    Given only the following Chat records exist:
      | #      | Session               |
      | chat_1 | {sid_AAAAAAAAAAAAAAA} |

    When I send a POST request to "/portal/api/chats/{chat_1}/validate/email?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key  | value     |
      | code | some code |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.code.errors[0].message" should be equal to "Email should not be validated."

  Scenario: I send empty validation code
    Given only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |

    When I send a POST request to "/portal/api/chats/{chat_1}/validate/email?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.code.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I try to validate email with wrong code
    Given only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |

    When I send a POST request to "/portal/api/chats/{chat_1}/validate/email?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key  | value     |
      | code | some code |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.code.errors[0].message" should be equal to "Wrong email validation code."

  Scenario: I regenerate email validation code
    Given only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |
    And I set chat email validation code "correct code" for chat "{chat_1}"

    When I send a POST request to "/portal/api/chats/{chat_1}/validate/email/regenerate?dpsid={sid_AAAAAAAAAAAAAAA}"
    Then the response status code should be 204
    And the response should be empty

    When I send a POST request to "/portal/api/chats/{chat_1}/validate/email?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key  | value        |
      | code | correct code |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.code.errors[0].message" should be equal to "Wrong email validation code."

  Scenario: I validate email successfully
    Given only the following Chat records exist:
      | #      | Person             | Session               |
      | chat_1 | {user@deskpro.dev} | {sid_AAAAAAAAAAAAAAA} |
    And I set chat email validation code "correct code" for chat "{chat_1}"

    When I send a POST request to "/portal/api/chats/{chat_1}/validate/email?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key  | value        |
      | code | correct code |
    Then the response status code should be 204
    And the response should be empty
    When I send a POST request to "/portal/api/chats/{chat_1}/validate/email?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key  | value        |
      | code | correct code |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.code.errors[0].message" should be equal to "Email is already validated."
