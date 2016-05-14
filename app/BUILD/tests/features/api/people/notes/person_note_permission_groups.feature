@people
Feature: /people/{id}/notes endpoint endpoint
  I want to check permission groups

  Background:
    Given I install the api data set
    And my request is authenticated
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

  @reinstall
  Scenario: I have no person note permissions
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
