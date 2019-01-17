@new
Feature: /people/{id}/notes endpoint endpoint
  I want to check permission groups

  Background:
    Given I'm authenticated as "agent"
    And "user@deskpro.dev" user exists
    And there are no "Permission" records
    And only the following "PersonNote" records exist:
      | #   | person | agent   | note  |
      | pn1 | {user} | {agent} | note1 |
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"

  Scenario: I have no person note permissions
    When I send a GET request to "/api/v2/people/{user}/notes"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/people/{user}/notes"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/people/{user}/notes/{pn1}"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/people/{user}/notes/{pn1}"
    Then the response status code should be 403

  Scenario: I create an person note
    Given I set permission "agent_people.notes" = 1 for "registered" usergroup
    When I send a POST request to "/api/v2/people/{user}/notes" with body:
    """
{
  "note": "my note"
}
    """
    Then the response status code should be 201

    When I send a PUT request to "/api/v2/people/{user}/notes/{pn1}"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/people/{user}/notes/{pn1}"
    Then the response status code should be 200

  Scenario: Admin has all permissions
    Given I'm authenticated as "admin"
    And I set permission "agent_people.notes" = 0 for "registered" usergroup
    And I add "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

    When I send a POST request to "/api/v2/people/{user}/notes" with body:
    """
{
  "note": "my note"
}
    """
    Then the response status code should be 201

    When I send a PUT request to "/api/v2/people/{user}/notes/{pn1}"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/people/{user}/notes/{pn1}"
    Then the response status code should be 200

  Scenario: Admin has all safe permissions
    Given I'm authenticated as "admin"
    And I set permission "agent_people.notes" = 0 for "registered" usergroup
    And I remove "admin" usergroup relation "agent_all_perms"
    And I add "admin" usergroup relation "agent_all_safe_perms"

    When I send a POST request to "/api/v2/people/{user}/notes" with body:
    """
{
  "note": "my note"
}
    """
    Then the response status code should be 201

    When I send a PUT request to "/api/v2/people/{user}/notes/{pn1}"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/people/{user}/notes/{pn1}"
    Then the response status code should be 200
