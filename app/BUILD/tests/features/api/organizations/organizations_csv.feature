@new
Feature:
  To export filtered list of organizations to CSV file
  As an API user
  I need /organizations/csv endpoint

  Background:
    Given I'm authenticated as agent
    And I have permissions to use organizations
    And only the following Organization records exist:
      | #         | Name                  |
      | microsoft | Microsoft Corporation |
      | yahoo     | Yahoo Inc             |

  Scenario: I retrieve list of organizations in CSV format
    When I send a GET request to "/api/v2/organizations/csv?count=200"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements
