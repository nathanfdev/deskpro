Feature: /ticket_filter_sets endpoint
  To CRUD deskpro filters sets
  As a developer
  I want an endpoint for filters

  Background:
    Given I install the "api" data set
    And my request is authenticated

  @reinstall
  Scenario: Successfully create a filter set
    When I send a POST request to "/api/v2/ticket_filter_sets" with body:
    """
{
    "title": "Awaiting Agent",
    "display_order": 0
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/ticket_filter_sets/1"

  Scenario: Successfully create a filter
    When I send a POST request to "/api/v2/filters" with body:
    """
{
"title": "My Sales Tickets",
"filter_set": 1,
"term": {
  "type": "composite",
  "op": "and",
  "terms": [
    {
      "type": "agent",
      "op": "is",
      "options": {
        "agent_ids": [
          "me"
        ]
      }
    },
    {
      "type": "ticket_status",
      "op": "is",
      "options": {
        "status": [
          "awaiting_agent"
        ]
      }
    },
    {
      "type": "department",
      "op": "is",
      "options": {
        "department_ids": [
          2
        ]
      }
    }
  ]
},
"display_order": 5
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/filters/1"
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My Sales Tickets"

  Scenario: I GET filter sets
    When I send a GET request to "/api/v2/ticket_filter_sets"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should exist
    And the JSON node "data[0]" should exist
    And the JSON node "data[0].title" should exist
    And the JSON node "data[0].title" should be equal to "Awaiting Agent"
    And the JSON node "data[0].display_order" should be equal to 0
    And the JSON node "data[0].filters[0].term.type" should be equal to "composite"
    And the JSON node "data[0].filters[0].term.op" should be equal to "and"
    And the JSON node "data[0].filters[0].term.terms[0].type" should be equal to "agent"
    And the JSON node "data[0].filters[0].term.terms[0].options.agent_ids[0]" should be equal to "me"
    And the JSON node "data[0].filters[0].links.self" should be equal to "/api/v2/filters/1"
