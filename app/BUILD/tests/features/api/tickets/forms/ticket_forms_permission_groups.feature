@tickets
Feature: /ticket_forms endpoint
  I want to check permission groups

  Background:
    Given I install the api data set
    And my request is authenticated
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

  Scenario: I have no ticket permissions
    When I send a POST request to "/api/v2/ticket_forms/agent"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/ticket_forms/agent/1"
    Then the response status code should be 403

  Scenario: Scenario: I grant tickets create permission
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup
    Given I set permission "agent_tickets.create" = 1 for "registered" usergroup
    Given I grant department 1 permission of "tickets" app for "admin"

    When I send a POST request to "/api/v2/ticket_forms/agent"
    Then the response status code should be 201

    When I send a PUT request to "/api/v2/ticket_forms/agent/1"
    Then the response status code should be 403

  Scenario: Scenario: I grant tickets edit permission
    Given I set permission "agent_tickets.modify_own" = 1 for "registered" usergroup
    When I send a PUT request to "/api/v2/ticket_forms/agent/2"
    Then the response status code should be 204
