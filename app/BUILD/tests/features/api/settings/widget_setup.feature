Feature: Widget Setup

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I get initial widget configuration
    When I send a GET request to "/api/v2/widget/setup"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.url.widget_loader" should exist
    And the JSON node "data.url.widget_bundle" should exist
    And the JSON node "data.url.helpdesk" should exist
    And the JSON node "data.enabled_on_portal" should be equal to 0

    And the JSON node "data.settings.global.company.name" should exist
    And the JSON node "data.settings.global.company.logo" should exist
    And the JSON node "data.settings.global.chat.require_login" should be equal to 0
    And the JSON node "data.settings.global.chat.email_validation" should be equal to 0

    And the JSON node "data.settings.brand.widget.type" should be equal to "column"
    And the JSON node "data.settings.brand.widget.position" should be equal to "right"
    And the JSON node "data.settings.brand.widget.agent_polling_timeout" should be equal to 0
    And the JSON node "data.settings.brand.button.size" should be equal to "medium"
    And the JSON node "data.settings.brand.button.name" should be equal to "Help"
    And the JSON node "data.settings.brand.button.name" should be equal to "Help"
    And the JSON node "data.settings.brand.button.colors.background" should be equal to "#62ad8c"
    And the JSON node "data.settings.brand.button.colors.text" should be equal to "#ffffff"
    And the JSON node "data.settings.brand.chat.enabled" should be equal to 1
    And the JSON node "data.settings.brand.chat.request_user_info" should be equal to 1
    And the JSON node "data.settings.brand.chat.proactive" should be equal to 1
    And the JSON node "data.settings.brand.chat.popup.title" should be equal to "Customer Support"
    And the JSON node "data.settings.brand.chat.popup.message" should be equal to "Need help? Just reply to start a live chat with one of our team."
    And the JSON node "data.settings.brand.chat.popup.reply_type" should be equal to "buttons"
    And the JSON node "data.settings.brand.chat.begin_mode" should be equal to "form"
    And the JSON node "data.settings.brand.chat.waiting_timeout" should be equal to 30
    And the JSON node "data.settings.brand.ticket.select_department" should be equal to "custom"
    And the JSON node "data.settings.brand.ticket.default_department" should be equal to 0

  Scenario: I get initial widget code
    When I send a GET request to "/api/v2/widget/code"
    And the response status code should be 200
    And the response should contain "DESKPRO_WIDGET_LOADER::BEGIN"
    And the response should contain "DESKPRO_WIDGET_LOADER::END"
    And the response should contain "DP_HELPDESK_URL"
    And the response should contain "request_user_info"

  Scenario: I update global widget configuration
    When I send a POST request to "/api/v2/widget/setup" with body:
    """
    {
      "global": {
        "chat": {
          "email_validation": false,
          "require_login": true
        }
      },
      "brand": {
        "widget": {
          "type": "bubble",
          "position": "left",
          "agent_polling_timeout": 400
        },
        "button": {
          "name": "Help",
          "size": "medium"
        },
        "chat": {
          "enabled": true,
          "begin_mode": "form",
          "waiting_timeout": 40,
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
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/widget/setup"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.settings.global.chat.require_login" should be equal to 1
    # Test disabled according to https://trello.com/c/x3cmtaoD
    #And the JSON node "data.settings.global.chat.email_validation" should be equal to 1
    And the JSON node "data.settings.global.chat.email_validation" should be equal to 0
    And the JSON node "data.enabled_on_portal" should be equal to 0

    And the JSON node "data.settings.brand.widget.type" should be equal to "bubble"
    And the JSON node "data.settings.brand.widget.position" should be equal to "left"
    And the JSON node "data.settings.brand.widget.agent_polling_timeout" should be equal to 400
    And the JSON node "data.settings.brand.button.name" should be equal to "Help"
    And the JSON node "data.settings.brand.button.size" should be equal to "medium"
    And the JSON node "data.settings.brand.chat.enabled" should be equal to "1"
    And the JSON node "data.settings.brand.chat.begin_mode" should be equal to "form"
    And the JSON node "data.settings.brand.chat.waiting_timeout" should be equal to 40
    And the JSON node "data.settings.brand.chat.popup.reply_type" should be equal to "buttons"
    And the JSON node "data.settings.brand.ticket.select_department" should be equal to "custom"

  Scenario: I apply chat widget to the portal
    When I send a POST request to "/api/v2/widget/portal/apply" with body:
        """
    {
      "global": {
        "chat": {
          "email_validation": false,
          "require_login": false
        }
      },
      "brand": {
        "widget": {
          "type": "column",
          "position": "right",
          "agent_polling_timeout": 400
        },
        "button": {
          "name": "Edited Help",
          "size": "large"
        },
        "chat": {
          "enabled": false,
          "begin_mode": "conversation",
          "waiting_timeout": 40,
          "popup": {
            "reply_type": "buttons"
          }
        },
        "ticket": {
          "select_department": "default",
          "default_department": 2
        }
      }
    }
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/widget/setup"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.settings.global.chat.require_login" should be equal to 0
    And the JSON node "data.settings.global.chat.email_validation" should be equal to 0
    And the JSON node "data.enabled_on_portal" should be equal to 1

    And the JSON node "data.settings.brand.widget.type" should be equal to "column"
    And the JSON node "data.settings.brand.widget.position" should be equal to "right"
    And the JSON node "data.settings.brand.widget.agent_polling_timeout" should be equal to 400
    And the JSON node "data.settings.brand.button.name" should be equal to "Edited Help"
    And the JSON node "data.settings.brand.button.size" should be equal to "large"
    And the JSON node "data.settings.brand.chat.enabled" should be equal to 0
    And the JSON node "data.settings.brand.chat.begin_mode" should be equal to "conversation"
    And the JSON node "data.settings.brand.chat.waiting_timeout" should be equal to 40
    And the JSON node "data.settings.brand.chat.popup.reply_type" should be equal to "buttons"
    And the JSON node "data.settings.brand.ticket.select_department" should be equal to "default"
    And the JSON node "data.settings.brand.ticket.default_department" should be equal to 2

  Scenario: I apply chat widget to the portal
    When I send a POST request to "/api/v2/widget/portal/remove"
    Then the response status code should be 204

    When I send a GET request to "/api/v2/widget/setup"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.enabled_on_portal" should be equal to 0

  Scenario: I check the widget code after changes
    When I send a GET request to "/api/v2/widget/code"
    And the response status code should be 200
    And the response should contain "DESKPRO_WIDGET_LOADER::BEGIN"
    And the response should contain "DESKPRO_WIDGET_LOADER::END"
    And the response should contain "DP_HELPDESK_URL"
    And the response should contain "agent_polling_timeout"
