@new
Feature: Ticket SLA logs

  Background:
    Given I'm authenticated as admin
    And I create a Ticket record and reference it as ticket
    And I have an Sla record referenced as sla

  Scenario: I create a ticket SLA and check the logs
    Given I reset the "{ticket}" ticket logs
    When I send a POST request to "/api/v2/tickets/{ticket}/ticket_slas" with body:
"""
{
  "sla": ~sla~,
  "sla_status": "warning"
}
    """
    Then the response status code should be 201
    And the "{ticket}" ticket should have "changed_sla_status" log

  Scenario: I delete a ticket SLA and check logs
    Given I send a POST request to "/api/v2/tickets/{ticket}/ticket_slas" with body:
"""
{
  "sla": ~sla~,
  "sla_status": "warning"
}
    """
    And I reset the "{ticket}" ticket logs
    When I send a DELETE request to "/api/v2/tickets/{ticket}/ticket_slas/{lastCreatedId}"
    Then the response status code should be 200
    And the "{ticket}" ticket should have "changed_slas" log
