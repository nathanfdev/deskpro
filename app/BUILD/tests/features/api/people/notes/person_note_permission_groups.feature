Feature: /people/{id}/notes endpoint endpoint
  I want to check permission groups

  Background:
    Given I install the api data set
    And I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"

  Scenario: I have no person note permissions
    Given my request is authenticated to "agent"
    When I send a GET request to "/api/v2/people/1/notes"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/people/1/notes"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/people/1/notes/1"
    Then the response status code should be 404

    When I send a DELETE request to "/api/v2/people/1/notes/1"
    Then the response status code should be 404

  Scenario: I create an person note
    Given I set permission "agent_people.notes" = 1 for "registered" usergroup
    And my request is authenticated to "agent"
    When I send a POST request to "/api/v2/people/1/notes" with body:
    """
{
  "note": "my note"
}
    """
    Then the response status code should be 201

    When I send a PUT request to "/api/v2/people/1/notes/1"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/people/1/notes/1"
    Then the response status code should be 200

  Scenario: Admin is allmighty and they doesn't care about permission groups
    Given my request is authenticated to "admin"
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

    When I send a POST request to "/api/v2/people/1/notes" with body:
    """
{
  "note": "my note"
}
    """
    Then the response status code should be 201

    When I send a PUT request to "/api/v2/people/1/notes/2"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/people/1/notes/2"
    Then the response status code should be 200
