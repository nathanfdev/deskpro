@new
Feature: /webhooks/tickets/{webhook}/invocation resource
  To CRUD Webhooks
  As a developer
  I want a REST API Resource

  Background:
    Given there are no "TicketWebhook" records
    And I'm authenticated as admin

  Scenario Outline: I create and invoke a webhook
    Given I send a POST request to "/api/v2/webhooks/tickets" with body:
    """
{
  "title":"<title>",
  "payload_decoder":"json",
  "is_enabled":true,
  "search_terms": [
      {
        "type" : "FilterLabels",
        "options" : {
          "labels": [
            "gina",
            "lina"
          ]
        }
      }
  ],
  "actions":{
    "version":1,
    "actions":[
      {
        "type": "SetHold",
        "options":{
          "is_hold": true
        }
      }
    ]
  }
}
    """
    And the response status code should be 201
    And I save the JSON node "data.auth_id" as placeholder "webhook_slug"
    When I send a POST request to "/api/v2/webhooks/tickets/~webhook_slug~/invocation" with body:
    """
{
  "title":"<title>",
  "is_enabled":true
}
    """
    Then the response status code should be 200
    Examples:
      | title |
      | my title |
