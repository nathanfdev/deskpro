@new
Feature: /voice_accounts endpoint

  Background:
    Given I'm authenticated as admin

  Scenario: I retrieve a list of twilio accounts
    Given only the following VoiceAccount records exist:
      | #  | AccountName | AccountSid | AuthToken |
      | a1 | Account 1   | Sid1       | Token1    |
      | a2 | Account 2   | Sid2       | Token2    |
      | a3 | Account 3   | Sid3       | Token3    |

    When I send a GET request to "/api/v2/voice_accounts"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].account_name" should be equal to the string "Account 1"
    And the JSON node "data[1].account_name" should be equal to the string "Account 2"
    And the JSON node "data[2].account_name" should be equal to the string "Account 3"

  Scenario: I create a new twilio account
    When I send a POST request to "/api/v2/voice_accounts" with body:
    """
{
  "account_name": "My account",
  "account_sid": "My sid",
  "auth_token": "My token"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.account_name" should be equal to "My account"
    And the JSON node "data.account_sid" should be equal to "My sid"
    And the JSON node "data.auth_token" should be equal to "My token"
    And the JSON node "data.auth_token" should exist

  Scenario: I update twilio account
    Given only the following VoiceAccount records exist:
      | #  | AccountName | AccountSid | AuthToken |
      | a1 | Account 1   | Sid1       | Token1    |

    When I send a PUT request to "/api/v2/voice_accounts/{a1}" with body:
    """
{
  "account_name": "My edited account",
  "account_sid": "My edited sid",
  "auth_token": "My edited token"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_accounts/{a1}"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{a1}"
    And the JSON node "data.account_name" should be equal to "My edited account"
    And the JSON node "data.account_sid" should be equal to "My edited sid"
    And the JSON node "data.auth_token" should be equal to "My edited token"

  Scenario: I delete twilio account
    Given only the following VoiceAccount records exist:
      | #  | AccountName | AccountSid | AuthToken |
      | a1 | Account 1   | Sid1       | Token1    |
      | a2 | Account 2   | Sid2       | Token2    |

    When I send a DELETE request to "/api/v2/voice_accounts/{a1}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/voice_accounts"
    And the JSON node "data" should have 1 element

  Scenario: Duplicate account SID validation
    Given only the following VoiceAccount records exist:
      | #  | AccountName | AccountSid | AuthToken |
      | a1 | Account 1   | Sid1       | Token1    |

    When I send a POST request to "/api/v2/voice_accounts" with body:
    """
{
  "account_name": "My account",
  "account_sid": "Sid1",
  "auth_token": "My token"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.account_sid.errors[0].code" should be equal to the string "unique_entity"
