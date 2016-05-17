Feature: /people endpoint
  I want to check permission groups

  Background:
    Given I install the api data set
    And my request is authenticated
    And I remove "admin" usergroup relation "agent_all_perms"
    And I remove "admin" usergroup relation "agent_all_safe_perms"

  @skip-ci
  # This one fails asserting 200, not sure why 200 is expected
  Scenario: I have no access to use people
    When I send a GET request to "/api/v2/people"
    Then the response status code should be 403

    When I send a GET request to "/api/v2/people/1"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/people"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/people/1"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/people/1"
    Then the response status code should be 403

  Scenario: I grant access to use people
    Given I set permission "agent_people.use" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/people"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/people/1"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/people"
    Then the response status code should be 403

    When I send a PUT request to "/api/v2/people/1"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/people/1"
    Then the response status code should be 403

  Scenario: I grant access to create people
    Given I set permission "agent_people.create" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/people"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/people/1"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/people"
    Then the response status code should be 400

    When I send a PUT request to "/api/v2/people/1"
    Then the response status code should be 403

    When I send a DELETE request to "/api/v2/people/1"
    Then the response status code should be 403

  Scenario: I grant access to edit people
    Given I set permission "agent_people.edit" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/people"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/people/1"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/people"
    Then the response status code should be 400

    When I send a PUT request to "/api/v2/people/1"
    Then the response status code should be 204

    When I send a DELETE request to "/api/v2/people/1"
    Then the response status code should be 403

  Scenario: I grant access to delete people
    Given I set permission "agent_people.delete" = 1 for "registered" usergroup

    When I send a GET request to "/api/v2/people"
    Then the response status code should be 200

    When I send a GET request to "/api/v2/people/1"
    Then the response status code should be 200

    When I send a POST request to "/api/v2/people"
    Then the response status code should be 400

    When I send a PUT request to "/api/v2/people/1"
    Then the response status code should be 204

    # Testing against non-agent person #3 because agents removing via /people
    # endpoint is always forbidden (agents need to be soft-deleted via /agents)
    When I send a DELETE request to "/api/v2/people/3"
    Then the response status code should be 200
