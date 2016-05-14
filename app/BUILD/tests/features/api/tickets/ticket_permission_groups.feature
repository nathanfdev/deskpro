@basic @tickets
Feature: /tickets endpoint
  I want to check permission groups

  Background:
    Given I install the api data set
    And my request is authenticated
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

  @reinstall
  Scenario: I have no access to use tickets
    When I send a GET request to "/api/v2/tickets"
    And the response status code should be 403

    When I send a GET request to "/api/v2/tickets/2"
    And the response status code should be 403

    When I send a POST request to "/api/v2/tickets"
    And the response status code should be 403

    When I send a PUT request to "/api/v2/tickets/2"
    And the response status code should be 403

    When I send a DELETE request to "/api/v2/tickets/2"
    And the response status code should be 403

  Scenario: I grant use tickets permission
    Given I set permission "agent_tickets.use" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/tickets"
    And the response status code should be 200

    When I send a GET request to "/api/v2/tickets/1"
    And the response status code should be 403
    When I send a GET request to "/api/v2/tickets/2"
    And the response status code should be 200

    When I send a POST request to "/api/v2/tickets"
    And the response status code should be 403

    When I send a PUT request to "/api/v2/tickets/2"
    And the response status code should be 403

    When I send a DELETE request to "/api/v2/tickets/2"
    And the response status code should be 403

  Scenario: I grant tickets create permission
    Given I set permission "agent_tickets.create" = 1 for "registered" usergroup
    Given I grant department 1 permission of "tickets" app for "admin"

    When I send a GET request to "/api/v2/tickets"
    And the response status code should be 200

    When I send a GET request to "/api/v2/tickets/2"
    And the response status code should be 200

    When I send a POST request to "/api/v2/tickets"
    And the response status code should be 400

    When I send a PUT request to "/api/v2/tickets/2"
    And the response status code should be 403

    When I send a DELETE request to "/api/v2/tickets/2"
    And the response status code should be 403

  Scenario: I grant tickets modify own permission
    Given I set permission "agent_tickets.modify_own" = 1 for "registered" usergroup
    Given I grant department 1 permission of "tickets" app for "admin"

    When I send a GET request to "/api/v2/tickets"
    And the response status code should be 200

    When I send a GET request to "/api/v2/tickets/2"
    And the response status code should be 200

    When I send a POST request to "/api/v2/tickets"
    And the response status code should be 400

    When I send a PUT request to "/api/v2/tickets/2"
    And the response status code should be 204

    When I send a DELETE request to "/api/v2/tickets/2"
    And the response status code should be 403

  Scenario: I grant ticket delete permissions
    Given I set permission "agent_tickets.delete_own" = 1 for "registered" usergroup
    Given I grant department 1 permission of "tickets" app for "admin"

    When I send a GET request to "/api/v2/tickets"
    And the response status code should be 200

    When I send a GET request to "/api/v2/tickets/2"
    And the response status code should be 200

    When I send a POST request to "/api/v2/tickets"
    And the response status code should be 400

    When I send a PUT request to "/api/v2/tickets/2"
    And the response status code should be 204

    When I send a DELETE request to "/api/v2/tickets/2"
    And the response status code should be 200
