Feature: /organizations endpoint
  I want to check ticket permission groups

  Background:
    Given I install the api data set
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"

  Scenario: I have no organization permissions
    Given my request is authenticated to "agent"
    When I send a GET request to "/api/v2/organizations"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/organizations/1"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/organizations"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/organizations/1"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/organizations/1"
    Then the response status code should be 403

  Scenario: I grant access to create organization
    Given my request is authenticated to "agent"
    Given I set permission "agent_org.create" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/organizations"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/organizations/1"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/organizations"
    Then the response status code should be 400

    When I send a PUT request to "/api/v2/organizations/1"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/organizations/1"
    Then the response status code should be 403

  Scenario: I grant access to edit organization
    Given my request is authenticated to "agent"
    Given I set permission "agent_org.edit" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/organizations"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/organizations/1"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/organizations"
    Then the response status code should be 400

    When I send a PUT request to "/api/v2/organizations/1"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/organizations/1"
    Then the response status code should be 403

  Scenario: I grant access to delete organization
    Given my request is authenticated to "agent"
    Given I set permission "agent_org.delete" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/organizations"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/organizations/1"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/organizations"
    Then the response status code should be 400

    When I send a PUT request to "/api/v2/organizations/1"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/organizations/1"
    Then the response status code should be 200

  Scenario: Admin is allmighty and they doesn't care about permission groups
    Given my request is authenticated to "admin"
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

    When I send a GET request to "/api/v2/organizations"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/organizations/2"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/organizations"
    Then the response status code should be 400

    When I send a PUT request to "/api/v2/organizations/2"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/organizations/2"
    Then the response status code should be 200
