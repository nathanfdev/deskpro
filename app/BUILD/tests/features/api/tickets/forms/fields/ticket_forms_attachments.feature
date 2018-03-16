@new
Feature: /ticket_forms
  I want to check attachment fields

  Background:
    Given I'm authenticated as admin
    And I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    And I create blob with auth code "BBBBBBBBBBBBBBBBBB"
    And the only default ticket layout exists with fields:
      | agent_layout |
      | attachments  |
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |
    And only the following TicketMessage records exist:
      | #  | Ticket | Person  | Message         |
      | m1 | {t1}   | {admin} | my text message |

  Scenario: I add attachments
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "attachments": [
    {"blob_auth": "AAAAAAAAAAAAAAAAAA"},
    {"blob_auth": "BBBBBBBBBBBBBBBBBB"}
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}/messages/{m1}"
    Then the response status code should be 200
    And the JSON node "data.attachments" should have 2 elements
    And the JSON node "data.attachments[0]" should exist
    And the JSON node "data.attachments[1]" should exist

  Scenario: I add inline attachments
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "attachments": [
    {"blob_auth": "AAAAAAAAAAAAAAAAAA", "is_inline": true},
    {"blob_auth": "BBBBBBBBBBBBBBBBBB", "is_inline": true}
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}/messages/{m1}"
    Then the response status code should be 200
    And the JSON node "data.attachments" should have 2 elements
    And the JSON node "data.attachments[0]" should exist
    And the JSON node "data.attachments[1]" should exist

  Scenario: I delete attachment
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "attachments": [
    {"blob_auth": "AAAAAAAAAAAAAAAAAA"}
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}/messages/{m1}"
    Then the response status code should be 200
    And the JSON node "data.attachments" should have 1 element

  Scenario: I clear attachments
    Given only the following TicketAttachment records exist:
      | Ticket | Person  | Message | Blob                      |
      | {t1}   | {admin} | {m1}    | {blob_AAAAAAAAAAAAAAAAAA} |
      | {t1}   | {admin} | {m1}    | {blob_BBBBBBBBBBBBBBBBBB} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "attachments": []
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}/messages/{m1}"
    Then the response status code should be 200
    And the JSON node "data.attachments" should have 0 elements

  Scenario: I check unique validation
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "attachments": [
    {"blob_auth": "AAAAAAAAAAAAAAAAAA"},
    {"blob_auth": "AAAAAAAAAAAAAAAAAA"}
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.attachments.errors[0].code" should be equal to "not_unique_collection"
