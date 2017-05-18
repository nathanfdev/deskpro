@new
Feature: Api tags and modes
  To grant and restrict access based on different login type
  and to have permissions based on api_key

  Background:
    Given I'm authenticated as "admin"

  Scenario: I send request to denied for key controller
    Given I set apiModes to "session" for "DeskPRO\Bundle\ApiBundle\Controller\NotificationController"
    And I set tags for my key to "-*"
    When I send a GET request to "/api/v2/notify/setup/action-alerts"
    Then the response should be in JSON
    And the response status code should be 403

  Scenario: I send request to allowed for key by mode and rejected by tags controller
    Given I set apiModes to "key" for "DeskPRO\Bundle\ApiBundle\Controller\NotificationController"
    And I set tags for my key to "-*"
    Then the response should be in JSON
    And the response status code should be 403

  Scenario: I send request to allowed for key controller
    Given I set apiModes to "key" for "DeskPRO\Bundle\ApiBundle\Controller\NotificationController"
    And I set tags for my key to "*"
    When I send a GET request to "/api/v2/notify/setup/action-alerts"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data.clients" should exist

  Scenario: I check api for tags giving me tags list
    Given I set tags for my key to "*"
    When I send a GET request to "/api/v2/api_tags/{apiKey}/flatten"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node data should exist
    And the JSON node "data[0]" should be equal to "*"

  Scenario: I check I can change tags via api
    Given I set tags for my key to "*"
    When I send a PUT request to "/api/v2/api_tags/{apiKey}" with body:
    """
{ "tags": "*,-tags.put" }
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/api_tags/{apiKey}/flatten"
    Then the response should be in JSON
    And the JSON node data should exist
    And the JSON node "data[0]" should be equal to "*"
    And the JSON node "data[1]" should be equal to "-tags.put"

  Scenario: I check I can change tags via api and they optimized
    Given I set tags for my key to "*"
    When I send a PUT request to "/api/v2/api_tags/{apiKey}" with body:
    """
{ "tags": "*,tags.put" }
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/api_tags/{apiKey}/flatten"
    Then the response should be in JSON
    And the JSON node data should exist
    And the JSON node "data[0]" should be equal to "*"
    And the JSON node "data[1]" should not exist
