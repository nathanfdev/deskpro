Feature: Api tags and modes
  To grant and restrict access based on different login type
  and to have permissions based on api_key

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I send request to denied for key controller
    Given I set apiModes to "session" for "DeskPRO\Bundle\ApiBundle\Controller\NotificationController"
    When I send a GET request to "/api/v2/notify/setup/action-alerts"
    Then the response should be in JSON
    And the response status code should be 403

  Scenario: I send request to allowed for key controller
    Given I set apiModes to "key" for "DeskPRO\Bundle\ApiBundle\Controller\NotificationController"
    When I send a GET request to "/api/v2/notify/setup/action-alerts"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.clients" should exist

  Scenario: I send request to allowed for key by mode and rejected by tags controller
    Given I set apiModes to "key" for "DeskPRO\Bundle\ApiBundle\Controller\NotificationController"
    And My key have no access to any controller or action