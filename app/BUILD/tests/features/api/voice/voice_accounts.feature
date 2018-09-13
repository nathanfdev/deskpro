@new
Feature: /voice_accounts endpoint

  Background:
    Given I'm authenticated as admin
    And the setting "beta_features.voice" is set to 1

  Scenario: I retrieve a list of twilio accounts
    Given only the following TwilioVoiceAccount records exist:
      | #  | AccountName | AccountId | AuthToken |
      | a1 | Account 1   | Sid1      | Token1    |
      | a2 | Account 2   | Sid2      | Token2    |
      | a3 | Account 3   | Sid3      | Token3    |
    And only the following PlivoVoiceAccount records exist:
      | #  | AccountName | AccountId | AuthToken |
      | a4 | Account 4   | Sid4      | Token4    |
      | a5 | Account 5   | Sid5      | Token5    |
      | a6 | Account 6   | Sid6      | Token6    |

    When I send a GET request to "/api/v2/voice_accounts?order_by=id&order_dir=asc"
    Then the response status code should be 200
    And the JSON node "data" should have 6 elements
    And the JSON node "data[0].account_name" should be equal to the string "Account 1"
    And the JSON node "data[1].account_name" should be equal to the string "Account 2"
    And the JSON node "data[2].account_name" should be equal to the string "Account 3"
    And the JSON node "data[3].account_name" should be equal to the string "Account 4"
    And the JSON node "data[4].account_name" should be equal to the string "Account 5"
    And the JSON node "data[5].account_name" should be equal to the string "Account 6"

  Scenario Outline: I create a new account
    When I send a POST request to "/api/v2/voice_accounts/<type>" with body:
    """
{
  "account_name": "My account",
  "account_id": "My sid",
  "auth_token": "My token"
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.account_name" should be equal to "My account"
    And the JSON node "data.account_id" should be equal to "My sid"
    And the JSON node "data.auth_token" should be equal to "My token"
    And the JSON node "data.auth_token" should exist

    Examples:
      | type   |
      | twilio |
      | plivo  |

  Scenario Outline: I update an account
    Given only the following <entity> records exist:
      | #  | AccountName | AccountId | AuthToken |
      | a1 | Account 1   | Sid1      | Token1    |

    When I send a PUT request to "/api/v2/voice_accounts/<type>/{a1}" with body:
    """
{
  "account_name": "My edited account",
  "account_id": "My edited sid",
  "auth_token": "My edited token"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/voice_accounts/{a1}"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{a1}"
    And the JSON node "data.account_name" should be equal to "My edited account"
    And the JSON node "data.account_id" should be equal to "My edited sid"
    And the JSON node "data.auth_token" should be equal to "My edited token"

    Examples:
      | entity             | type   |
      | TwilioVoiceAccount | twilio |
      | PlivoVoiceAccount  | plivo  |

  Scenario Outline: I delete twilio account
    Given no TwilioVoiceAccount records exist
    And no PlivoVoiceAccount records exist
    And only the following <entity> records exist:
      | #  | AccountName | AccountId | AuthToken |
      | a1 | Account 1   | Sid1      | Token1    |
      | a2 | Account 2   | Sid2      | Token2    |

    When I send a DELETE request to "/api/v2/voice_accounts/{a1}"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/voice_accounts"
    And the JSON node "data" should have 1 element

    Examples:
      | entity             |
      | TwilioVoiceAccount |
      | PlivoVoiceAccount  |

  Scenario: Duplicate account SID validation
    Given only the following TwilioVoiceAccount records exist:
      | #  | AccountName | AccountId | AuthToken |
      | a1 | Account 1   | Sid1      | Token1    |

    When I send a POST request to "/api/v2/voice_accounts/twilio" with body:
    """
{
  "account_name": "My account",
  "account_id": "Sid1",
  "auth_token": "My token"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.account_id.errors[0].code" should be equal to the string "unique_entity"

  Scenario: I try to create twilio account via /api/v2/voice_accounts
    When I send a POST request to "/api/v2/voice_accounts" with body:
    """
{
  "account_name": "My account",
  "account_id": "Sid1",
  "auth_token": "My token"
}
    """
    Then the response status code should be 405

  Scenario: I try to edit twilio account via /api/v2/voice_accounts
    Given only the following TwilioVoiceAccount records exist:
      | #  | AccountName | AccountId | AuthToken |
      | a1 | Account 1   | Sid1      | Token1    |

    When I send a PUT request to "/api/v2/voice_accounts/{a1}" with body:
    """
{
  "account_name": "My account"
}
    """
    Then the response status code should be 405

  Scenario: I try to edit twilio account via another provider's controller
    Given only the following TwilioVoiceAccount records exist:
      | #  | AccountName | AccountId | AuthToken |
      | a1 | Account 1   | Sid1      | Token1    |

    When I send a PUT request to "/api/v2/voice_accounts/plivo/{a1}" with body:
    """
{
  "account_name": "My account",
}
    """
    Then the response status code should be 404
