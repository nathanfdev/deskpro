@new
Feature: /person_contact_data endpoint
  To retrieve DeskPRO person contact data
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as admin

  Scenario: I retrieve a list of person contact data
    When I send a GET request to "/api/v2/people/{admin}/contact_data"
    Then the response should be in JSON
    And the response status code should be 200
