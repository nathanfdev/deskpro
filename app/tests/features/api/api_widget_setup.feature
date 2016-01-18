Feature: Widget Setup

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I get widget configuration
    When I send a GET request to "/api/v2/widget/setup"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.url.widget_loader" should exist
    And the JSON node "data.url.widget_bundle" should exist
    And the JSON node "data.url.helpdesk" should exist
    And the JSON node "data.company.name" should exist
    And the JSON node "data.settings.global.chat.require_login" should be equal to "0"
    And the JSON node "data.settings.global.chat.email_validation" should be equal to "0"
    And the JSON node "data.settings.brand" should exist
    And the JSON node "data.settings.brand.widget" should not exist
    And the JSON node "data.settings.brand.button" should not exist
    And the JSON node "data.settings.brand.chat" should not exist

  Scenario: I update global widget configuration
    When I send a POST request to "/api/v2/widget/setup" with body:
    """
    {
      "global": {
        "chat": {
          "email_validation": true,
          "require_login": true
        }
      },
      "brand": {
        "widget": {
          "type": "bubble",
          "position": "left"
        },
        "button": {
          "name": "Help",
          "size": "medium"
        },
        "chat": {
          "enabled": true,
          "begin_mode": "form",
          "popup": {
            "reply_type": "buttons"
          }
        },
        "ticket": {
          "select_department": "custom"
        }
      }
    }
    """
    Then the response should be in JSON
    And the response status code should be 204
    When I send a GET request to "/api/v2/widget/setup"
    And the JSON node "data.settings.global.chat.require_login" should be equal to "1"
    And the JSON node "data.settings.global.chat.email_validation" should be equal to "1"
    And the JSON node "data.settings.brand.widget.type" should be equal to "bubble"
    And the JSON node "data.settings.brand.widget.position" should be equal to "left"
    And the JSON node "data.settings.brand.button.name" should be equal to "Help"
    And the JSON node "data.settings.brand.button.size" should be equal to "medium"
    And the JSON node "data.settings.brand.chat.enabled" should be equal to "1"
    And the JSON node "data.settings.brand.chat.begin_mode" should be equal to "form"
    And the JSON node "data.settings.brand.chat.popup.reply_type" should be equal to "buttons"
    And the JSON node "data.settings.brand.ticket.select_department" should be equal to "custom"
