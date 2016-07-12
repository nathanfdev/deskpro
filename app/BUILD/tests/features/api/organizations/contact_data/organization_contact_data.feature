@new
Feature: /organizations/{id}/contact_data endpoint
  To retrieve DeskPRO organization contact data
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "agent"
    And only the following "Organization" records exist:
      | #    | name       | summary                                    |
      | org1 | Vector ltd | Vector is a common fake org name in Russia |

  Scenario: I retrieve a list of organization contact data
    When I send a GET request to "/api/v2/organizations/{org1}/contact_data"
    Then the response should be in JSON
    And the response status code should be 200
