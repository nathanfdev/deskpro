@basic @tickets
Feature: /{id}/ticket_slas endpoint
  To CRUD DeskPRO ticket's SLAs
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I create a ticket SLA
    When I send a POST request to "/api/v2/tickets/3/ticket_slas" with body:
"""
{
  "sla": 2,
  "sla_status": "warning"
}
    """
    Then the response status code should be 201

  Scenario: I get created ticket SLA
    When I send a GET request to "/api/v2/tickets/3/ticket_slas?include=ticket,sla"
    Then the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should have 1 element
    And print last JSON response
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].sla_status" should be equal to "warning"
    And the JSON node "data[0].sla" should be equal to 2
    And the JSON node "data[0].ticket" should be equal to 3
    And the JSON node "linked.ticket" should exist
    And the JSON node "linked.sla" should exist

  Scenario: I try create a ticket SLA with the same SLA
    When I send a POST request to "/api/v2/tickets/3/ticket_slas" with body:
"""
{
  "sla": 2,
  "sla_status": "fail"
}
    """
    Then the response status code should be 204

  Scenario: I get created ticket SLA
    When I send a GET request to "/api/v2/tickets/3/ticket_slas?include=ticket,sla"
    Then the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].sla_status" should be equal to "fail"
    And the JSON node "data[0].sla" should be equal to 2
    And the JSON node "data[0].ticket" should be equal to 3
    And the JSON node "linked.ticket" should exist
    And the JSON node "linked.sla" should exist

  Scenario: I try update non-existing ticket SLA
    When I send a PUT request to "/api/v2/tickets/3/ticket_slas" with body:
"""
{
  "sla": 1,
  "sla_status": "ok"
}
    """
    Then the response status code should be 404

  Scenario: I update ticket SLA
    When I send a PUT request to "/api/v2/tickets/3/ticket_slas" with body:
"""
{
  "sla": 2,
  "sla_status": "ok"
}
    """
    Then the response status code should be 204

  Scenario: I get updated ticket SLA
    When I send a GET request to "/api/v2/tickets/3/ticket_slas?include=ticket,sla"
    Then the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].sla_status" should be equal to "ok"
    And the JSON node "data[0].sla" should be equal to 2
    And the JSON node "data[0].ticket" should be equal to 3
    And the JSON node "linked.ticket" should exist
    And the JSON node "linked.sla" should exist

  Scenario: I try to get SLAs for non-existing ticket
    When I send a GET request to "/api/v2/tickets/3000/ticket_slas"
    Then the response status code should be 404
    And the JSON node "status" should be equal to 404
    And the JSON node "message" should be equal to "Not found"

  Scenario: I try to get ticket SLA for non-existing parent SLA
    When I send a GET request to "/api/v2/tickets/3/ticket_slas/by_sla/3000"
    Then the response status code should be 404
    And the JSON node "status" should be equal to 404
    And the JSON node "message" should be equal to "Ticket SLA for ticket ID=3 and SLA ID=3000 not found"
