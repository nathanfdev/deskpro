Feature: Production errors logging with System Alerts
  In order to collect issues information on production instances
  As a developer
  I want to log PHP errors and unhandled exceptions

  Background:
    Given I install the api data set
    And I log in as admin from the portal
    And I go to "/new-agent/"
    And I have no logged system alert events

  @basic
  Scenario: I access API controller which doesn't produce any errors
    And I go to "/api/v2/people"
    Then the response status code should be 200
    And the JSON node "data" should exist
    And there should be no system alert events

  Scenario: I access API controller throwing an HTTP exception
    When I send a GET request to "/api/v2/_internal/incidents-demo/http-exception?confirm=Yes_I_use_it_for_testing"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "message" should exist
    And there should be no system alert events

  Scenario: I access API controller producing a PHP notice
    When I send a GET request to "/api/v2/_internal/incidents-demo/php-notice?confirm=Yes_I_use_it_for_testing"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data" should be equal to "This action produced a PHP notice"
    And there should be 1 "php_error" system alert

  Scenario: I access API controller producing a PHP fatal error
    When I send a GET request to "/api/v2/_internal/incidents-demo/php-fatal-error?confirm=Yes_I_use_it_for_testing"
    Then the response status code should be 500
    And there should be 1 "php_error" system alert

  Scenario: I access API controller throwing an exception
    When I send a GET request to "/api/v2/_internal/incidents-demo/exception?confirm=Yes_I_use_it_for_testing"
    Then the response status code should be 500
    And the response should be in JSON
    And the JSON node "message" should exist
    And there should be 1 "exception" system alert