@new
Feature: Client devices

  Background:
    Given I'm authenticated as admin
    And no ClientDevice records exist

  Scenario: I start with no devices
    Given there are no ClientDevice records
    When I send a GET request to "/api/v2/client_devices/mobile"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 0 elements

  Scenario: I create a new device
    When I send a POST request to "/api/v2/client_devices/mobile" with body:
    """
{
  "device_id": "my_device",
  "device_type": "ios.iphone",
  "device_agent": "my device agent string",
  "device_name": "device alpha"
}
    """
    Then the response status code should be 201

    And the JSON node "data.device_id" should be equal to "my_device"
    And the JSON node "data.device_type" should be equal to "ios.iphone"
    And the JSON node "data.device_agent" should be equal to "my device agent string"
    And the JSON node "data.device_name" should be equal to "device alpha"
    And the JSON node "data.app_type" should be equal to "mobile"
    And the JSON node "data.person" should be equal to "{admin}"
    And the JSON node "data.can_notify" should be false
    And the JSON node "data.notify_token" should be null
    And the JSON node "data.date_created" should exist

  Scenario: I get a client device
    Given only the following ClientDevice records exist:
      | #  | Device Id | Device Type |
      | d1 | my_device | ios.iphone  |

    When I send a GET request to "/api/v2/client_devices/mobile/my_device"
    Then the response should be in JSON
    And the response status code should be 200

  Scenario: I update the device
    Given only the following ClientDevice records exist:
      | #  | Device Id | Device Type |
      | d1 | my_device | ios.iphone  |

    When I send a PUT request to "/api/v2/client_devices/mobile/my_device" with body:
    """
{
  "device_agent": "my NEW device agent string"
}
    """
    Then the response should be empty
    And the response status code should be 204

    When I send a GET request to "/api/v2/client_devices/mobile/my_device"
    Then the JSON node "data.device_agent" should be equal to "my NEW device agent string"

  Scenario: I update the device via PUT to register
    Given only the following ClientDevice records exist:
      | #  | Device Id | Device Type |
      | d1 | my_device | ios.iphone  |

    When I send a PUT request to "/api/v2/client_devices/mobile/register/my_device" with body:
    """
{
  "device_agent": "my OTHER NEW device agent string"
}
    """
    Then the response should be empty
    And the response status code should be 204

    When I send a GET request to "/api/v2/client_devices/mobile/my_device"
    Then the JSON node "data.device_agent" should be equal to "my OTHER NEW device agent string"

  Scenario Outline: I register a new device via PUT to register
    When I send a PUT request to "/api/v2/client_devices/mobile/register/<device_id>" with body:
    """
{
  "device_type": "ios.iphone",
  "device_agent": "my device agent string",
  "device_name": "device beta"
}
    """
    Then the response status code should be 201

    When I send a GET request to "/api/v2/client_devices/mobile/<device_id>"
    Then the response should be in JSON
    And the response status code should be 200

    Examples:
      | device_id                            |
      | 123                                  |
      | 1FE0BD4C-EED3-4BDC-8C17-A2C1025D51C3 |

  Scenario: My list of devices should show both devices
    Given only the following ClientDevice records exist:
      | #  | Device Id  | Device Type | App Type | Person  |
      | d1 | my_device1 | ios.iphone  | mobile   | {admin} |
      | d2 | my_device2 | ios.iphone  | mobile   | {admin} |

    When I send a GET request to "/api/v2/client_devices/mobile"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements

  Scenario: I enable notifications on a device
    Given only the following ClientDevice records exist:
      | #  | Device Id                            | Device Type |
      | d1 | 1FE0BD4C-EED3-4BDC-8C17-A2C1025D51C3 | ios.iphone  |

    When I send a PUT request to "/api/v2/client_devices/mobile/1FE0BD4C-EED3-4BDC-8C17-A2C1025D51C3" with body:
    """
{
  "notification_token": "FOOBAR"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/client_devices/mobile/1FE0BD4C-EED3-4BDC-8C17-A2C1025D51C3"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.can_notify" should be true
    And the JSON node "data.notify_token" should be equal to "FOOBAR"

  Scenario: I update a device via PUT to register
    Given only the following ClientDevice records exist:
      | #  | Device Id                            | Device Type |
      | d1 | 1FE0BD4C-EED3-4BDC-8C17-A2C1025D51C3 | ios.iphone  |

    When I send a PUT request to "/api/v2/client_devices/mobile/register/1FE0BD4C-EED3-4BDC-8C17-A2C1025D51C3" with body:
    """
{
  "notification_token": "FOOBARBAZ"
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/client_devices/mobile/1FE0BD4C-EED3-4BDC-8C17-A2C1025D51C3"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.can_notify" should be true
    And the JSON node "data.notify_token" should be equal to "FOOBARBAZ"

  Scenario: I attempt to create a duplicate device
    Given only the following ClientDevice records exist:
      | #  | Device Id | Device Type | Person  | App type |
      | d1 | my_device | ios.iphone  | {admin} | mobile   |

    When I send a POST request to "/api/v2/client_devices/mobile" with body:
    """
{
  "device_id": "my_device",
  "device_type": "ios.iphone",
  "device_agent": "my device agent string",
  "device_name": "device alpha"
}
    """
    Then the response status code should be 400

    When I send a GET request to "/api/v2/client_devices/mobile"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 1 element
