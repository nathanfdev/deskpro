Feature: /person_contact_data endpoint
  To retrieve DeskPRO person contact data
  As an API user
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I retrieve a list of person contact data
    When I send a GET request to "/api/v2/people/1/contact_data"
    Then the response should be in JSON
    And the response status code should be 200
