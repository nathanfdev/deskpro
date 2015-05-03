Feature: /filters endpoint
  To CRUD deskpro filters
  As a developer
  I want an endpoint for filters

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: Successfully create a filter
    When I send a POST request to "/api/v2/filters" with body:
    """
{
  "title": "My Sales Tickets",
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
    And the JSON node "data.term.type" should be equal to "composite"
    And the JSON node "data.term.op" should be equal to "and"
    And the JSON node "data.term.terms[0].type" should be equal to "agent"
    And the JSON node "data.term.terms[0].options.agent_ids[0]" should be equal to "me"
    And the JSON node "data.links.self" should be equal to "/api/v2/filters/1"

  Scenario: I GET a single filter
    When I send a GET request to "/api/v2/filters/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "My Sales Tickets"
    And the JSON node "data.term.type" should be equal to "composite"
    And the JSON node "data.term.op" should be equal to "and"
    And the JSON node "data.term.terms[0].type" should be equal to "agent"
    And the JSON node "data.term.terms[0].options.agent_ids[0]" should be equal to "me"
    And the JSON node "data.links.self" should be equal to "/api/v2/filters/1"

  Scenario: I GET filters
    When I send a GET request to "/api/v2/filters"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "meta.count" should be equal to 1
    And the JSON node "meta.page" should be equal to 1
    And the JSON node "meta.total_pages" should be equal to 1
    And the JSON node "meta.total_count" should be equal to 1
    And the JSON node "data" should exist
    And the JSON node "data[0].title" should be equal to "My Sales Tickets"
    And the JSON node "data[0].term.type" should be equal to "composite"
    And the JSON node "data[0].term.op" should be equal to "and"
    And the JSON node "data[0].term.terms[0].type" should be equal to "agent"
    And the JSON node "data[0].term.terms[0].options.agent_ids[0]" should be equal to "me"
    And the JSON node "data[0].links.self" should be equal to "/api/v2/filters/1"

  Scenario: I modify a filter
    When I send a PUT request to "/api/v2/filters/1" with body:
    """
    {
      "title": "NEW TITLE"
    }
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: I verify the resource from the PUT above was actually updated
    When I send a GET request to "/api/v2/filters/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "NEW TITLE"
    And the JSON node "data.term.type" should be equal to "composite"
    And the JSON node "data.links.self" should be equal to "/api/v2/filters/1"

  Scenario: I fail to GET a filter
    When I send a GET request to "/api/v2/filters/101"
    Then the response status code should be 404

  Scenario: I DELETE a filter
    When I send a DELETE request to "/api/v2/filters/1"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I fail to GET the deleted filter
    When I send a GET request to "/api/v2/filters/1"
    Then the response status code should be 404

  @reinstall
  Scenario: If there is a missing term op, we use default op
    When I send a POST request to "/api/v2/filters" with body:
    """
{
    "title": "My Sales Tickets",
    "term": {
        "type": "agent",
        "options": {
            "agent_ids": [1,2]
        }
    }
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the JSON node "data.id" should exist
    And the JSON node "data.title" should exist
    And the JSON node "data.term.type" should be equal to "agent"
    And the JSON node "data.term.op" should be equal to "is"
    And the JSON node "data.links.self" should be equal to "/api/v2/filters/1"

  Scenario: Create a filter that successfully uses all of the terms
    When I send a POST request to "/api/v2/filters" with body:
    """
{
  "title": "Big Test",
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
        "type": "agent_team",
        "op": "is",
        "options": {
          "agent_team_ids": [
            "me",
            6
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
      },
      {
        "type": "person_email",
        "op": "is",
        "options": {
          "email": "foo@bar.ca"
        }
      },
      {
        "type": "ticket_custom_data",
        "op": "is",
        "options": {
          "field_id": 5,
          "input": "test"
        }
      },
      {
        "type": "ticket_participant",
        "op": "is",
        "options": {
          "person_ids": [
            1,
            4
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
      }
    ]
  },
  "display_order": 5
}
    """
    Then the response should be in JSON
    And the response status code should be 201
    And the header "Location" should be equal to "/api/v2/filters/2"
    And the JSON node "data" should exist
    And the JSON node "data.title" should be equal to "Big Test"
    And the JSON node "data.term.type" should be equal to "composite"