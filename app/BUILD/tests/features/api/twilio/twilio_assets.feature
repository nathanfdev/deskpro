@new
Feature: Twilio assets

  Background:
    Given I'm authenticated as admin
    And only the following TwilioQueue records exist:
      | #  | Name    | Routing Model |
      | q1 | Queue 1 | round_robin   |

  Scenario: I create text asset
    When I send a PUT request to "/api/v2/twilio_queues/{q1}" with body:
    """
{
  "greet_asset": {
    "name": "My asset",
    "type": "text",
    "text": "my text",
    "language": "en-GB"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/twilio_queues/{q1}"
    Then the JSON node "data.greet_asset.name" should be equal to the string "My asset"
    And the JSON node "data.greet_asset.type" should be equal to the string "text"
    And the JSON node "data.greet_asset.text" should be equal to the string "my text"
    And the JSON node "data.greet_asset.language" should be equal to the string "en-GB"

  Scenario Outline: I create blob asset
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    When I send a PUT request to "/api/v2/twilio_queues/{q1}" with body:
    """
{
  "greet_asset": {
    "name": "My asset",
    "type": "<type>",
    "blob": {
      "blob_auth": "AAAAAAAAAAAAAAAAAA"
    }
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/twilio_queues/{q1}"
    Then the JSON node "data.greet_asset.name" should be equal to the string "My asset"
    And the JSON node "data.greet_asset.type" should be equal to the string "<type>"
    And the JSON node "data.greet_asset.blob" should exist
    And the JSON node "data.greet_asset.blob.blob_auth" should be equal to the string "AAAAAAAAAAAAAAAAAA"

    Examples:
      | type   |
      | upload |
      | record |

  Scenario Outline: Asset name validation
    When I send a PUT request to "/api/v2/twilio_queues/{q1}" with body:
    """
{
  "greet_asset": {
    "name": "",
    "type": "<type>"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.greet_asset.fields.name.errors[0].code" should be equal to the string "required"

    Examples:
      | type   |
      | text   |
      | upload |
      | record |

  Scenario: Asset type required validation
    When I send a PUT request to "/api/v2/twilio_queues/{q1}" with body:
    """
{
  "greet_asset": {
    "name": "My asset",
    "type": ""
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.greet_asset.fields.type.errors[0].code" should be equal to the string "required"

  Scenario: Asset type bad choice validation
    When I send a PUT request to "/api/v2/twilio_queues/{q1}" with body:
    """
{
  "greet_asset": {
    "name": "My asset",
    "type": "unknown"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.greet_asset.fields.type.errors[0].code" should be equal to the string "bad_choice"

  Scenario: Text asset fields validation
    When I send a PUT request to "/api/v2/twilio_queues/{q1}" with body:
    """
{
  "greet_asset": {
    "name": "My asset",
    "type": "text",
    "text": "",
    "language": ""
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.greet_asset.fields.text.errors[0].code" should be equal to the string "required"
    And the JSON node "errors.fields.greet_asset.fields.language.errors[0].code" should be equal to the string "required"

  Scenario Outline: Upload/record asset blob required validation
    When I send a PUT request to "/api/v2/twilio_queues/{q1}" with body:
    """
{
  "greet_asset": {
    "name": "My asset",
    "type": "<type>",
    "blob": ""
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.greet_asset.fields.blob.errors[0].code" should be equal to the string "required"

    Examples:
      | type   |
      | upload |
      | record |

  Scenario Outline: Upload/record asset unknown blob validation
    Given there are no Blob records in the DB
    And I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    When I send a PUT request to "/api/v2/twilio_queues/{q1}" with body:
    """
{
  "greet_asset": {
    "name": "My asset",
    "type": "<type>",
    "blob": "BBBBBBBBBBBBBBBBBB"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.greet_asset.fields.blob.errors[0].code" should be equal to the string "required"

    Examples:
      | type   |
      | upload |
      | record |
