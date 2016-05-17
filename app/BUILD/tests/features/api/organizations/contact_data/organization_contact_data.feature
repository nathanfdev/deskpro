Feature: /organizations/{id}/contact_data endpoint
  To retrieve DeskPRO organization contact data
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve a list of organization contact data
    When I send a GET request to "/api/v2/organizations/1/contact_data"
    Then the response should be in JSON
    And the response status code should be 200
