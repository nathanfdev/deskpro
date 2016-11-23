@new
Feature: Documentation

  Background:
    Given I'm authenticated as user

  Scenario: I GET ApiDoc
    When I send a GET request to "/api/v2/doc"
    Then the response status code should be 200
