@new
Feature: Custom scripts located in /app/scripts dir

  Background:
    Given no Person records exist

  Scenario: I check user script as guest
    When I go to "/scripts/user/example/hello"
    Then the response status code should be 200
    And the response should contain "Hello, world"

  Scenario: I check user script as user
    Given I'm authenticated as user

    When I go to "/scripts/user/example/hello"
    Then the response status code should be 200
    And the response should contain "Hello, User User"
