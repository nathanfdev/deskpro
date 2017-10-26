@new @webhooks
Feature: /webhooks/tickets/{webhook}/invocation resource
  To CRUD Webhooks
  As a developer
  I want a REST API Resource

  Background:
    Given there are no "TicketWebhook" records
    And there are no custom ticket fields defined
    And there are no "Ticket" records
    Given I'm authenticated as admin

  Scenario Outline: I create and invoke a webhook with a json payload
    Given I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "<actual_subject>",
  "person": ~admin~,
  "labels": ["webhook-label-1", "label-2"]
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
        "type" : "FilterLabels",
        "options" : {
          "labels": ["webhook-label-1", "webhook-label-2"]
        }
      }
  ],
  "terms": [
    [{
      "type" : "CheckWebhookVar",
      "op": "isset",
      "options" : {
        "name": "data.webhook.is_enabled"
      }
    }]
  ],
  "actions":{
    "version":1,
    "actions":[
      {
        "type": "SetSubject",
        "options":{
          "subject": "<expected_subject>"
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
  "webhook" : {
    "is_enabled":true
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/~ticket_id~"
    Then the JSON node "data.subject" should be equal to "<expected_subject>"
    Examples:
      | webhook_title | expected_subject                  | actual_subject |
      | my title      | Sample Ticket Modified By Webhook | Sample Ticket  |

  Scenario Outline: I create and invoke a webhook with a form payload
    Given I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "<actual_subject>",
  "person": ~admin~,
  "labels": ["webhook-label-2", "label-2"]
}
    """
    And I save the JSON node "data.id" as placeholder "ticket_id"
    And I send a POST request to "/api/v2/webhooks/tickets" with body:
    """
{
  "title":"<webhook_title>",
  "payload_decoder":"form",
  "is_enabled":true,
  "search_terms": [
      {
        "type" : "FilterLabels",
        "options" : {
          "labels": ["webhook-label-2", "label-2"]
        }
      }
  ],
  "terms": [
    [{
      "type" : "CheckWebhookVar",
      "op": "isset",
      "options" : {
        "name": "data.webhook.is_enabled"
      }
    }]
  ],
  "actions":{
    "version":1,
    "actions":[
      {
        "type": "SetSubject",
        "options":{
          "subject": "<expected_subject>"
        }
      }
    ]
  }
}
    """
    And the response status code should be 201
    And I save the JSON node "data.auth_id" as placeholder "webhook_slug"

    When I send a POST request to "/api/v2/webhooks/tickets/~webhook_slug~/invocation" with parameters:
      | key                         | value   |
      | person_registration[name][] | cthulhu |
      | webhook[is_enabled]         | true    |
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/~ticket_id~"
    Then the JSON node "data.subject" should be equal to "<expected_subject>"
    Examples:
      | webhook_title | expected_subject                  | actual_subject |
      | my title      | Modified By Webhook w/ Form       | Sample Ticket  |


  Scenario Outline: I create and invoke a webhook with search term variables
    Given I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "<actual_subject>",
  "person": ~admin~,
  "labels": ["webhook-label-2", "label-2"]
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
        "type" : "FilterLabels",
        "options" : {
          "labels": ["twig:{{data.label}}", "label-2"]
        }
      }
  ],
  "terms": [
    [{
      "type" : "CheckWebhookVar",
      "op": "isset",
      "options" : {
        "name": "data.webhook.is_enabled"
      }
    }]
  ],
  "actions":{
    "version":1,
    "actions":[
      {
        "type": "SetSubject",
        "options":{
          "subject": "<expected_subject>"
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
  "webhook" : {
    "is_enabled":true
  },
  "label" : "webhook-label-2"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/~ticket_id~"
    Then the JSON node "data.subject" should be equal to "<expected_subject>"
    Examples:
      | webhook_title | expected_subject                  | actual_subject |
      | my title      | Modified By Webhook w/ Form       | Sample Ticket  |
