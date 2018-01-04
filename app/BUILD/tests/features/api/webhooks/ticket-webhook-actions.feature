@new @webhook-actions
Feature: /webhooks/tickets/{webhook}/invocation resource
  I can delete a value from a DataList field by invoking a webhook
  As a developer
  I want a REST API Resource

  Background:
    Given there are no "TicketWebhook" records
    And there are no custom ticket fields defined
    And there are no "Ticket" records
    Given I'm authenticated as admin

  Scenario Outline: I create and invoke a webhook with a json payload
    Given I send a POST request to "/api/v2/ticket_custom_fields" with a json body:
    """
{
  "title":"<field_title>",
  "is_enabled":true,
  "alias": "<field_alias>",
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\DataList"
}
    """
    And I save the JSON node "data.id" as placeholder "field_id"
    And I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "<ticket_subject>",
  "person": ~admin~,
  "fields": {
    "<field_alias>": [
      "JIR-1",
      "JIR-2",
      "JIR-3",
      "JIR-4",
      "JIR-5"
    ]
  }
}
    """
    And I save the JSON node "data.id" as placeholder "ticket_id"
    And I send a POST request to "/api/v2/webhooks/tickets" with body:
    """
{
  "title":"<webhook_title>",
  "payload_decoder":"json",
  "is_enabled":true,
  "search_terms": [
    {
      "type" : "FilterTicketField",
      "op": "contains",
      "options" : {
        "field_id": "~field_id~",
        "value": "twig:{{webhook.data.id}}"
      }
    }
  ],
  "triggers": [
    {
      "terms": [
        [{
          "type" : "CheckWebhookVar",
          "op": "is",
          "options" : {
            "name": "webhook.data.webhookEvent",
            "value": "jira:issue_deleted"
          }
        }]
      ],
      "actions":{
        "version":1,
        "actions":[
          {
            "type": "SetTicketField",
            "options":{
              "op": "unset-list",
              "with_formatter": true,
              "field_id": "~field_id~",
              "value": "{{webhook.data.id}}"
            }
          }
        ]
      }
    }
  ]
}
    """
    And the response status code should be 201
    And I save the JSON node "data.auth_id" as placeholder "webhook_slug"

    When I send a POST request to "/api/v2/webhooks/~webhook_slug~/invocation" with body:
    """
{
  "id": "JIR-5",
  "timestamp": "2009-09-09T00:08:36.796-0500",
  "webhookEvent": "jira:issue_deleted"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/~ticket_id~"
    Then the JSON list node "data.fields.~ticket_id~.value" should not contain "JIR-5"
    Examples:
      | webhook_title | ticket_subject | field_alias | field_title  |
      | my title      | Sample Ticket  |  jira       | Jira Tickets |

