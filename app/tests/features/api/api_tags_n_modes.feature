Feature: Api tags and modes
  To grant and restrict access based on different login type
  and to have permissions based on api_key

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I send any request to restricted for key controller
    Given I set apiModes to "session" for "DeskPRO\Bundle\ApiBundle\Controller\NotificationController"
    When I send a GET request to "/api/v2/notify/setup/action-alerts"
    Then the response should be in JSON
    And the response status code should be 403

  Scenario: I send any request to restricted for key controller
    Given I set apiModes to "key" for "DeskPRO\Bundle\ApiBundle\Controller\NotificationController"
    When I send a GET request to "/api/v2/notify/setup/action-alerts"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.clients" should exist