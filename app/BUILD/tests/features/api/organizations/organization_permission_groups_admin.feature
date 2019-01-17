@new
Feature: /organizations endpoint
  I want to check ticket permission groups admin

  Scenario: Admin has all permission groups
    Given I'm authenticated as "admin"
    And I add "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"
    And the following "Organization" records exist:
      | #    | name       | summary                                    |
      | org1 | Vector ltd | Vector is a common fake org name in Russia |

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
