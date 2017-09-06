@new @webhooks
Feature: /webhooks/tickets resource
  To CRUD Webhooks
  As a developer
  I want a REST API Resource

  Background:
    Given there are no "TicketWebhook" records
    And I'm authenticated as admin

  Scenario Outline: I create a webhook
    When I send a POST request to "/api/v2/webhooks/tickets" with body:
    """
{
  "title":"<title>",
  "payload_decoder":"json",
  "is_enabled":true,
  "terms": [
    [
      {
        "type" : "CheckTicketField",
        "op": "is",
        "options" : {
          "field_id": "field6",
          "value": "zorba"
        }
      }
    ]
  ],
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
    Then the response status code should be 201
    And the JSON node "data.auth_id" should not be null
    And the JSON node "data.search_terms" should be equal to node:
    """
    [
      {
        "type" : "label",
        "op": "is",
        "options" : {
          "label": [
            "gina",
            "lina"
          ]
        }
      }
    ]
    """

    And the JSON node "data.terms" should be equal to node:
    """
        {
          "version": 1,
          "terms": [
            {
              "set_terms": [
                {
                  "type": "CheckTicketFieldfield6",
                  "op": "is",
                  "options": {
                    "field_id": "field6",
                    "value": "zorba"
                  }
                }
              ]
            }
          ]
        }
    """

    And the JSON node "data.actions" should be equal to node:
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
