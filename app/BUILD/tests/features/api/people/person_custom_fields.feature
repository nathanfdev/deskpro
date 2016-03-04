Feature: /people/custom_fields endpoint
  To retrieve DeskPRO person custom fields
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve a list of custom fields
    When I send a GET request to "/api/v2/people/custom_fields"
    Then the response should be in JSON
    And the response status code should be 200
    And print last JSON response
