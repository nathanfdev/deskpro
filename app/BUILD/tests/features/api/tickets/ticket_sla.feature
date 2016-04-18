@basic @tickets
Feature: /tickets/{id}/slas endpoint
  To CRUD DeskPRO ticket's SLAs
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I create a ticket SLA
    When I send a POST request to "/api/v2/tickets/3/slas" with body:
"""
{
  "sla": 2,
  "sla_status": "warning"
}
    """
    Then the response status code should be 201

  Scenario: I gey created ticket SLA
    When I send a GET request to "/api/v2/tickets/3/slas" with body:
"""
{
  "sla": 1,
  "sla_status": "ok"
}
    """
    And print last JSON response
    Then the response status code should be 200
    And the JSON node "meta" should exist
    And the JSON node "data" should have 1 element
    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].sla_status" should be equal to "warning"
    And the JSON node "data[0].sla.id" should be equal to 2
    And the JSON node "data[0].ticket.id" should be equal to 3

