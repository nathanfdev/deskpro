@new
Feature: /organizations endpoint
  I want to check ticket permission groups

  Background:
    Given I'm authenticated as "agent"
    And I clear usergroup "registered" permissions
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"
    And the following "Organization" records exist:
      | #    | name       | summary                                    |
      | org1 | Vector ltd | Vector is a common fake org name in Russia |

  Scenario: I have no organization permissions

    When I send a GET request to "/api/v2/organizations"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/organizations/{org1}"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/organizations"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/organizations/{org1}"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/organizations/{org1}"
    Then the response status code should be 403

  Scenario: I grant access to create organization
    Given I set only "agent_org.create" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/organizations"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/organizations/{org1}"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/organizations"
    Then the response status code should be 400

    When I send a PUT request to "/api/v2/organizations/{org1}"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/organizations/{org1}"
    Then the response status code should be 403

  Scenario: I grant access to edit organization
    Given I set permission "agent_org.edit" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/organizations"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/organizations/{org1}"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/organizations"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/organizations/{org1}"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/organizations/{org1}"
    Then the response status code should be 403

  Scenario: I grant access to delete organization
    Given I set permission "agent_org.delete" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/organizations"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/organizations/{org1}"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/organizations"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/organizations/{org1}"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/organizations/{org1}"
    Then the response status code should be 200

  Scenario: Admin is allmighty and they doesn't care about permission groups
    Given I'm authenticated as "admin"
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

    When I send a GET request to "/api/v2/organizations"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/organizations/{org1}"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/organizations"
    Then the response status code should be 400

    When I send a PUT request to "/api/v2/organizations/{org1}"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/organizations/{org1}"
    Then the response status code should be 200
