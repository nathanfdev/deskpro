@new @webhooks
Feature: /webhooks/tickets resource
  To CRUD Webhook Triggers
  As a developer
  I want a REST API Resource

  Background:
    Given there are no "TicketWebhook" records
    And I'm authenticated as admin

  Scenario Outline: I create a trigger
    When I send a POST request to "/api/v2/webhooks/tickets" with body:
    """
{
  "title":"<title>",
  "payload_decoder":"json",
  "is_enabled":true,
  "search_terms": [
    {
      "type" : "FilterLabels",
      "op": "is",
      "options" : {
        "labels": [
          "gina",
          "lina"
        ]
      }
    }
  ]
}
    """
    Then the response status code should be 201
    And I save the JSON node "data.id" as placeholder "webhook_id"
    And I send a POST request to "/api/v2/webhooks/~webhook_id~/triggers" with body:
    """
{
    "terms": [
      [
        {
          "type" : "CheckWebhookVar",
          "op": "isset",
          "options" : {
            "name": "webhook.data.something.is_enabled"
          }
        }
      ],
      [
        {
          "type" : "CheckTicketField",
          "op": "is",
          "options" : {
            "field_id": "7",
            "value": "a value"
          }
        },
        {
          "type" : "CheckTicketField",
          "op": "is",
          "options" : {
            "field": "someAlias",
            "value": "another value"
          }
        }
      ]
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
    And I save the JSON node "data.id" as placeholder "webhook_trigger_id"
    When I send a GET request to "/api/v2/webhooks/~webhook_id~/triggers/~webhook_trigger_id~"
    Then the JSON node "data.terms" should be equal to node:
    """
        {
          "version": 1,
          "terms": [
            {
              "set_terms": [
                {
                  "type": "CheckWebhookVar",
                  "op": "isset",
                  "options": {
                    "name": "webhook.data.something.is_enabled"
                  }
                }
              ]
            },
            {
              "set_terms": [
                {
                  "type" : "CheckTicketField7",
                  "op": "is",
                  "options" : {
                    "field_id": "7",
                    "value": "a value"
                  }
                },
                {
                  "type": "CheckTicketField",
                  "op": "is",
                  "options": {
                    "field": "someAlias",
                    "value": "another value"
                  }
                }
              ]
            }
          ]
        }
    """

    And I send a GET request to "/api/v2/webhooks/tickets/~webhook_id~?include=ticket_trigger&inline_sideloads=1"
    Then the JSON node "data.triggers[0].terms" should be equal to node:
    """
        {
          "version": 1,
          "terms": [
            {
              "set_terms": [
                {
                  "type": "CheckWebhookVar",
                  "op": "isset",
                  "options": {
                    "name": "webhook.data.something.is_enabled"
                  }
                }
              ]
            },
            {
              "set_terms": [
                {
                  "type" : "CheckTicketField7",
                  "op": "is",
                  "options" : {
                    "field_id": "7",
                    "value": "a value"
                  }
                },
                {
                  "type": "CheckTicketField",
                  "op": "is",
                  "options": {
                    "field": "someAlias",
                    "value": "another value"
                  }
                }
              ]
            }
          ]
        }
    """

    And the JSON node "data.triggers[0].actions" should be equal to node:
    """
      {
        "version" : 1,
        "actions" : [
          {
            "type": "SetHold",
            "options": { "is_hold": "1" }
          }
        ]
      }
    """


    And the JSON node "data.is_enabled" should be true
    And the JSON node "data.payload_decoder" should be equal to "json"
    And the JSON node "data.title" should be equal to "<title>"

    Examples:
      | title |
      | my title |

  Scenario: I delete a trigger
    When I send a POST request to "/api/v2/webhooks/tickets" with body:
    """
{
  "title":"<title>",
  "payload_decoder":"json",
  "is_enabled":true,
  "search_terms": [
    {
      "type" : "FilterLabels",
      "op": "is",
      "options" : {
        "labels": [
          "gina",
          "lina"
        ]
      }
    }
  ]
}
    """
    And I save the JSON node "data.id" as placeholder "webhook_id"
    And I send a POST request to "/api/v2/webhooks/~webhook_id~/triggers" with body:
    """
{
    "terms": [
      [
        {
          "type" : "CheckWebhookVar",
          "op": "isset",
          "options" : {
            "name": "webhook.data.something.is_enabled"
          }
        }
      ],
      [
        {
          "type" : "CheckTicketField",
          "op": "is",
          "options" : {
            "field": "someAlias",
            "value": "another value"
          }
        }
      ]
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
    And I save the JSON node "data.id" as placeholder "webhook_trigger_id"
    And I send a GET request to "/api/v2/webhooks/~webhook_id~/triggers/~webhook_trigger_id~"
    And the response status code should be 200
    When I send a DELETE request to "/api/v2/webhooks/~webhook_id~/triggers/~webhook_trigger_id~"
    And the response status code should be 200
    And I send a GET request to "/api/v2/webhooks/~webhook_id~/triggers/~webhook_trigger_id~"
    Then the response status code should be 404
