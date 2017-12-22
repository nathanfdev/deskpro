@new @webhooks
Feature: /webhooks/tickets/{webhook}/invocation resource
  To CRUD Webhooks
  As a developer
  I want a REST API Resource

  Background:
    Given there are no "TicketWebhook" records
    And there are no "CustomDefTicket" records
    And there are no "Ticket" records
    And I'm authenticated as admin

  Scenario Outline: I create and invoke a webhook bound to custom data fields
    Given I send a POST request to "/api/v2/ticket_custom_fields" with a json body:
    """
{
  "title":"<custom_field_title>",
  "is_enabled":true,
  "alias": "<custom_field_alias>",
  "handler_class":"<custom_field_handler>"
}
    """
    And I save the JSON node "data.id" as placeholder "custom_field_id"
# TODO: investigate why enabling this steps makes the test suite fail on CI
#    And the only default ticket layout exists with fields:
#      | agent_layout                    |
#      | ~custom_field_id~ |
    And I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "<actual_subject>",
  "person": ~admin~,
  "labels": ["webhook-label-2", "label-2"],
  "fields": {
    "~custom_field_id~": <custom_field_value>
  }
}
    """
    And print last response body
    And I save the JSON node "data.id" as placeholder "ticket_id"
    And I send a POST request to "/api/v2/webhooks/tickets" with body:
    """
{
  "title":"<webhook_title>",
  "payload_decoder":"json",
  "is_enabled":true,
  "search_terms": [
      {
        "type" : "FilterTicketField~custom_field_id~",
        "op": "is",
        "options" : {
          "field_type": "~custom_field_id~",
           "value": "twig:{{webhook.data.field}}"
        }
      }
  ],
  "triggers": [
    {
      "terms": [
        [{
          "type" : "CheckWebhookVar",
          "op": "isset",
          "options" : {
            "name": "webhook.data.something.is_enabled"
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
  ]
}
    """
    And the response status code should be 201
    And I save the JSON node "data.auth_id" as placeholder "webhook_slug"

    When I send a POST request to "/api/v2/webhooks/tickets/~webhook_slug~/invocation" with body:
    """
{
  "something" : {
    "is_enabled":true
  },
  "field" : "lemmy"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/~ticket_id~"
    Then the JSON node "data.subject" should be equal to "<expected_subject>"
    Examples:
      | webhook_title | expected_subject                  | actual_subject | custom_field_title | custom_field_alias | custom_field_handler                                  | custom_field_value     |
#      | my title      | Modified By Webhook w/ Form       | Sample Ticket  |     Data list      |  webhookAliasOne   | Application\\DeskPRO\\CustomFields\\Handler\\DataList | ["lemmy", "motorhead"] |
      | my title      | Modified By Webhook w/ Form       | Sample Ticket  |     Data      |  webhookAliasData   | Application\\DeskPRO\\CustomFields\\Handler\\Data | "lemmy" |
