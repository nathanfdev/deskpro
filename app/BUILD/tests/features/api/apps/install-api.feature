@new @apps
Feature: install v2 apps
  As an API user
  I want to install v2 apps

  Background:
    Given there are no "App" records
    And I'm authenticated as agent

  Scenario: I install an application via the api
    Given I package the app from folder "resources/apps/state-tests"
    When I send a POST request to "/api/v2/apps" with content type "application/zip" and file "{lastPackagedApp}" as body
    Then the response status code should be 200
