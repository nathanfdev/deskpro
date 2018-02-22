@new @webhooks @webhook-actions
Feature: /webhooks/tickets/{webhook}/invocation resource
  I can trigger actions on a ticket by invoking a webhook
  As a developer
  I want a REST API Resource

  Background:
    Given there are no "TicketWebhook" records
    And there are no custom ticket fields defined
    And there are no "Ticket" records
    Given I'm authenticated as admin

  Scenario Outline: I can delete a value from a DataList field by invoking a webhook
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
        "field": "<field_alias>",
        "value": "twig:{{webhook.data.id}}"
      }
    }
  ]
}
    """
    And the response status code should be 201
    And I save the JSON node "data.auth_id" as placeholder "webhook_slug"
    And I save the JSON node "data.id" as placeholder "webhook_id"
    And I send a POST request to "/api/v2/webhooks/~webhook_id~/triggers" with body:
    """
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
              "field": "<field_alias>",
              "value": "{{webhook.data.id}}"
            }
          }
        ]
      }
    }
    """

    When I send a POST request to "/api/v2/webhooks/~webhook_slug~/invocation" with body:
    """
{
  "id": "JIR-5",
  "timestamp": "2009-09-09T00:08:36.796-0500",
  "webhookEvent": "jira:issue_deleted"
}
    """
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tickets/~ticket_id~"
    Then the JSON list node "data.fields.~field_id~.value" should not contain "JIR-5"
    Examples:
      | webhook_title | ticket_subject | field_alias | field_title  |
      | my title      | Sample Ticket  |  jira       | Jira Tickets |

  Scenario Outline: I can set a custom field field by invoking a webhook
    Given I send a POST request to "/api/v2/ticket_custom_fields" with a json body:
    """
{
  "title":"<field_title>",
  "is_enabled":true,
  "alias": "<field_alias>",
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\Text"
}
    """
    And I save the JSON node "data.id" as placeholder "field_id"
    And I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "<ticket_subject>",
  "person": ~admin~,
  "fields": {
    "<field_alias>": "<field_value_default>"
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
      "op": "is",
      "options" : {
        "field": "<field_alias>",
        "value": "<field_value_default>"
      }
    }
  ]
}
    """
    And I save the JSON node "data.auth_id" as placeholder "webhook_slug"
    And I save the JSON node "data.id" as placeholder "webhook_id"
    And I send a POST request to "/api/v2/webhooks/~webhook_id~/triggers" with body:
    """
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
              "op": "set",
              "with_formatter": <with_formatter>,
              "field": "<field_alias>",
              "value": "<field_value_expected>"
            }
          }
        ]
      }
    }
    """

    When I send a POST request to "/api/v2/webhooks/~webhook_slug~/invocation" with body:
    """
    {
      "id": "JIR-5",
      "timestamp": "2009-09-09T00:08:36.796-0500",
      "webhookEvent": "jira:issue_deleted"
    }
    """
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tickets/~ticket_id~"
    Then the JSON node "data.fields.~field_id~.value" should be equal to "<field_value_expected>"
    Examples:
      | webhook_title | ticket_subject | field_alias | field_title  | field_value_expected | with_formatter | field_value_default |
      | my title      | Sample Ticket  |  jira       | Jira Tickets | john                 |   false        |  default            |


  Scenario Outline: I can unset a custom field field by invoking a webhook
    Given I send a POST request to "/api/v2/ticket_custom_fields" with a json body:
    """
  {
    "title":"<field_title>",
    "is_enabled":true,
    "alias": "<field_alias>",
    "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\Text"
  }
    """
    And I save the JSON node "data.id" as placeholder "field_id"
    And I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "<ticket_subject>",
  "person": ~admin~,
  "fields": {
    "<field_alias>": "<field_value_default>"
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
      "op": "is",
      "options" : {
        "field": "<field_alias>",
        "value": "<field_value_default>"
      }
    }
  ]
}
    """
    And I save the JSON node "data.auth_id" as placeholder "webhook_slug"
    And I save the JSON node "data.id" as placeholder "webhook_id"
    And I send a POST request to "/api/v2/webhooks/~webhook_id~/triggers" with body:
    """
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
              "op": "unset",
              "value": null,
              "field": "<field_alias>"
            }
          }
        ]
      }
    }
    """

    And I send a GET request to "/api/v2/tickets/~ticket_id~"
    And the JSON node "data.fields.~field_id~.value" should be equal to "<field_value_default>"

    When I send a POST request to "/api/v2/webhooks/~webhook_slug~/invocation" with body:
    """
{
  "id": "JIR-5",
  "timestamp": "2009-09-09T00:08:36.796-0500",
  "webhookEvent": "jira:issue_deleted"
}
    """
    Then the response status code should be 200

    When I send a GET request to "/api/v2/tickets/~ticket_id~"
    And print last response body
    Then the JSON node "data.fields" should have 0 elements
    Examples:
      | webhook_title | ticket_subject | field_alias | field_title  | with_formatter | field_value_default |
      | my title      | Sample Ticket  |  jira       | Jira Tickets |   false        |  default            |



