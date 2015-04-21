Feature: /filters endpoint
  To CRUD deskpro filters
  As a developer
  I want an endpoint for filters

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: Successfully create a filter
    When I send a POST request to "/api/v2/filters" with body:
    """
    {
       "title": "My Sales Tickets",
       "term": {
          "type": "composite",
          "data": {
             "op": "and"
          },
          "terms": [
             {
                "type": "agent",
                "data": {
                   "op": "is",
                   "options": {
                      "agent_ids": [
                         "me"
                      ]
                   }
                }
             },
             {
                "type": "ticket_status",
                "data": {
                   "op": "is",
                   "options": {
                      "status": [
                         "awaiting_agent"
                      ]
                   }
                }
             },
             {
                "type": "department",
                "data": {
                   "op": "is",
                   "options": {
                      "department_ids": [
                         2
                      ]
                   }
                }
             }
          ]
       },
       "display_order": 5
    }
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should contain "/api/v2/filters/"
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My Sales Tickets"
    And the JSON node "data.term.type" should be equal to "composite"
    And the JSON node "data.term.data.op" should be equal to "and"
    And the JSON node "data.term.terms[0].type" should be equal to "agent"
    And the JSON node "data.term.terms[0].data.options.agent_ids[0]" should be equal to "me"
    And the JSON node "data.links.self" should exist