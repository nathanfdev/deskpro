@new
Feature: Documentation

  Background:
    Given I'm authenticated as user

  Scenario: I GET ApiDoc
    When I send a GET request to "/api/v2/doc"
    Then the response status code should be 200

  Scenario: I GET slate docs
    When I send a GET request to "/api/v2/man"
    Then the response status code should be 302
    And the header "Location" should be equal to "http://api.deskpro.com/"
