@new
Feature: /organization_notes endpoint

  Scenario: Admin has all permissions
    Given I'm authenticated as "admin"
    And I clear usergroup "registered" permissions
    And I add "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"
    And the following "Organization" records exist:
      | #    | name       | summary                                    |
      | org1 | Vector ltd | Vector is a common fake org name in Russia |

    When I send a POST request to "/api/v2/organizations/{org1}/notes" with body:
    """
    {
      "note": "my note"
    }
        """
    Then the response status code should be 201

    When I send a PUT request to "/api/v2/organizations/{org1}/notes/{lastCreatedId}"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/organizations/{org1}/notes/{lastCreatedId}"
    Then the response status code should be 200
