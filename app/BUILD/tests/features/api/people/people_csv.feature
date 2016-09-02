@new
Feature:
  To export filtered list of people to CSV file
  As an API user
  I need /people/csv endpoint

  Background:
    Given there are no "Person" records
    And I'm authenticated as agent

  Scenario: I retrieve list of people in CSV format
    When I send a GET request to "/api/v2/people/csv?count=200"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
