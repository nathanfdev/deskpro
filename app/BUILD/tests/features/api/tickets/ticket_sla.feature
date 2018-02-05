@new
Feature: /tickets/{id}/ticket_slas endpoint
  To CRUD DeskPRO ticket's SLAs
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin

  Scenario: I create a ticket SLA
    Given I have a Ticket record referenced as ticket
    And I have an SLA record with "SLA type" equal to "warning" referenced as sla
    When I send a POST request to "/api/v2/tickets/{ticket}/ticket_slas" with body:
"""
{
  "sla": ~sla~,
  "sla_status": "warning"
}
    """
    Then the response status code should be 201

  Scenario: I get created ticket SLA
    When I send a GET request to "/api/v2/tickets/{ticket}/ticket_slas?include=ticket,sla"
    Then the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].sla_status" should be equal to "warning"
    And the JSON node "data[0].sla" should be equal to "{sla}"
    And the JSON node "linked.ticket" should exist
    And the JSON node "linked.sla" should exist

  Scenario: I try create a ticket SLA with the same SLA
    When I send a POST request to "/api/v2/tickets/{ticket}/ticket_slas" with body:
"""
{
  "sla": ~sla~,
  "sla_status": "fail"
}
    """
    Then the response status code should be 204

  Scenario: I get created ticket SLA
    When I send a GET request to "/api/v2/tickets/{ticket}/ticket_slas?include=ticket,sla"
    Then the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].sla_status" should be equal to "fail"
    And the JSON node "data[0].sla" should be equal to "{sla}"
    And the JSON node "linked.ticket" should exist
    And the JSON node "linked.sla" should exist

  Scenario: I get ticket SLA by ticket ref
    When I send a GET request to "/api/v2/tickets/ref:{ticket:ref}/ticket_slas"
    And the response status code should be 200

  Scenario: I try update non-existing ticket SLA
    When I send a PUT request to "/api/v2/tickets/{ticket}/ticket_slas" with body:
"""
{
  "sla": 404404,
  "sla_status": "ok"
}
    """
    Then the response status code should be 404

  Scenario: I update ticket SLA
    When I send a PUT request to "/api/v2/tickets/{ticket}/ticket_slas" with body:
"""
{
  "sla": ~sla~,
  "sla_status": "ok"
}
    """
    Then the response status code should be 204

  Scenario: I get updated ticket SLA
    When I send a GET request to "/api/v2/tickets/{ticket}/ticket_slas?include=ticket,sla"
    Then the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].sla_status" should be equal to "ok"
    And the JSON node "data[0].sla" should be equal to "{sla}"
    And the JSON node "linked.ticket" should exist
    And the JSON node "linked.sla" should exist

  Scenario: I try to get SLAs for non-existing ticket
    When I send a GET request to "/api/v2/tickets/{ticket}404/ticket_slas"
    Then the response status code should be 404
    And the JSON node "status" should be equal to 404
    And the JSON node "message" should exist

  Scenario: I try to get ticket SLA for non-existing parent SLA
    When I send a GET request to "/api/v2/tickets/{ticket}/ticket_slas/by_sla/404404"
    Then the response status code should be 404
    And the JSON node "status" should be equal to 404
    And the JSON node "message" should exist

  Scenario: I delete ticket sla
    When I send a DELETE request to "/api/v2/tickets/{ticket}/ticket_slas/{lastCreatedId}"
    Then the response status code should be 200
