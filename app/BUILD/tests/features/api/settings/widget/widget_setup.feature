Feature: Widget Setup

  Background:
    Given I'm authenticated as "admin"
    And I have only default brand
    And only the following custom chat fields exist:
      | #   | Type |
      | cf1 | text |
      | cf2 | text |

  Scenario: I get initial widget configuration
    When I send a GET request to "/api/v2/settings/brands/{defaultBrandId}/widget/setup"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.url.widget_loader" should exist
    And the JSON node "data.url.widget_bundle" should exist
    And the JSON node "data.url.helpdesk" should exist
    And the JSON node "data.enabled_on_portal" should be equal to 0

    And the JSON node "data.settings.global.company.name" should exist
    And the JSON node "data.settings.global.company.logo" should exist
    And the JSON node "data.settings.global.chat.enabled" should be equal to 0
    And the JSON node "data.settings.global.chat.require_login" should be equal to 0
    And the JSON node "data.settings.global.chat.email_validation" should be equal to 0

    And the JSON node "data.settings.brand.widget.type" should be equal to the string "column"
    And the JSON node "data.settings.brand.widget.enabled" should be equal to true
    And the JSON node "data.settings.brand.widget.position" should be equal to the string "right"
    And the JSON node "data.settings.brand.widget.agent_polling_timeout" should be equal to 0
    And the JSON node "data.settings.brand.button.size" should be equal to the string "medium"
    And the JSON node "data.settings.brand.button.translations[0].language" should be equal to 1
    And the JSON node "data.settings.brand.button.translations[0].name" should be equal to the string "Chat Now"
    And the JSON node "data.settings.brand.button.translations[0].contact_us" should be equal to the string "Contact Us"
    And the JSON node "data.settings.brand.button.colors.background" should be equal to the string "#62ad8c"
    And the JSON node "data.settings.brand.button.colors.text" should be equal to the string "#ffffff"
    And the JSON node "data.settings.brand.chat.request_user_info" should be equal to 0
    And the JSON node "data.settings.brand.chat.proactive" should be equal to 1
    And the JSON node "data.settings.brand.chat.popup.translations[0].language" should be equal to 1
    And the JSON node "data.settings.brand.chat.popup.translations[0].title" should be equal to the string "Customer Support"
    And the JSON node "data.settings.brand.chat.popup.translations[0].message" should be equal to the string "Need help? Just reply to start a live chat with one of our team."
    And the JSON node "data.settings.brand.chat.popup.style" should be equal to the string "agent_text_button"
    And the JSON node "data.settings.brand.chat.begin_mode" should be equal to the string "form"
    And the JSON node "data.settings.brand.chat.waiting_timeout" should be equal to 150
    And the JSON node "data.settings.brand.chat.custom_fields" should have 2 elements
    And the JSON node "data.settings.brand.chat.custom_fields[0].id" should be equal to "{cf1}"
    And the JSON node "data.settings.brand.chat.custom_fields[0].display_order" should be equal to 0
    And the JSON node "data.settings.brand.chat.custom_fields[0].is_enabled" should be equal to 1
    And the JSON node "data.settings.brand.chat.custom_fields[1].id" should be equal to "{cf2}"
    And the JSON node "data.settings.brand.chat.custom_fields[1].display_order" should be equal to 0
    And the JSON node "data.settings.brand.chat.custom_fields[1].is_enabled" should be equal to 1
    And the JSON node "data.settings.brand.ticket.select_department" should be equal to the string "custom"
    And the JSON node "data.settings.brand.ticket.default_department" should be equal to 0

  Scenario: I get initial widget code
    When I send a GET request to "/api/v2/settings/brands/{defaultBrandId}/widget/code"
    And the response status code should be 200
    And the JSON node "data" should contain "DESKPRO_WIDGET_LOADER::BEGIN"
    And the JSON node "data" should contain "DESKPRO_WIDGET_LOADER::END"
    And the JSON node "data" should contain "pub/build/widget_loader.min.js"
    And the JSON node "data" should contain "dp-widget-loader"
    And the JSON node "data" should contain "helpdeskUrl"

  Scenario: I update global widget configuration
    When I send a POST request to "/api/v2/settings/brands/{defaultBrandId}/widget/setup" with body:
    """
{
  "enabled_on_portal": false,
  "settings": {
    "global": {
      "chat": {
        "enabled": true,
        "email_validation": true,
        "require_login": true
      }
    },
    "brand": {
      "widget": {
        "type": "bubble",
        "position": "left",
        "agent_polling_timeout": 400,
        "enabled": true
      },
      "button": {
        "translations": [
          {
            "language": 1,
            "name": "Help (edited)",
            "contact_us": "Contact us (edited)"
          },
          {
            "language": 2,
            "name": "Help (fr)",
            "contact_us": "Contact us (fr)"
          }
        ],
        "size": "medium"
      },
      "chat": {
        "begin_mode": "form",
        "waiting_timeout": 40,
        "select_department": "custom",
        "popup": {
          "translations": [
            {
              "language": 1,
              "title": "Customer Support (edited)",
              "message": "Need help? Just reply to start a live chat with one of our team. (edited)",
              "heading": "Ask us a question! (edited)",
              "subheading": "Our team are online and ready to help with your enquiries. Send us a message to get started. (edited)"
            },
            {
              "language": 2,
              "title": "Customer Support (fr)",
              "message": "Need help? Just reply to start a live chat with one of our team. (fr)",
              "heading": "Ask us a question! (fr)",
              "subheading": "Our team are online and ready to help with your enquiries. Send us a message to get started. (fr)"
            }
          ],
          "style": "agent_text_input"
        },
        "custom_fields": [
          {
            "id": ~cf1~,
            "display_order": 10,
            "is_enabled": true
          },
          {
            "id": ~cf2~,
            "display_order": 20,
            "is_enabled": false
          }
        ],
        "user_groups": [1, 2]
      },
      "ticket": {
        "select_department": "custom"
      }
    }
  }
}
    """
    Then the response status code should be 204
    And the response should be empty

    When I send a GET request to "/api/v2/settings/brands/{defaultBrandId}/widget/setup"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.settings.global.chat.enabled" should be equal to 1
    And the JSON node "data.settings.global.chat.require_login" should be equal to 1
    And the JSON node "data.settings.global.chat.email_validation" should be equal to 1
    And the JSON node "data.enabled_on_portal" should be equal to 0

    And the JSON node "data.settings.brand.widget.type" should be equal to the string "bubble"
    And the JSON node "data.settings.brand.widget.enabled" should be equal to true
    And the JSON node "data.settings.brand.widget.position" should be equal to the string "left"
    And the JSON node "data.settings.brand.widget.agent_polling_timeout" should be equal to 400
    And the JSON node "data.settings.brand.button.translations[0].language" should be equal to 1
    And the JSON node "data.settings.brand.button.translations[0].name" should be equal to the string "Help (edited)"
    And the JSON node "data.settings.brand.button.translations[0].contact_us" should be equal to the string "Contact us (edited)"
    And the JSON node "data.settings.brand.button.translations[1].name" should be equal to the string "Help (fr)"
    And the JSON node "data.settings.brand.button.translations[1].contact_us" should be equal to the string "Contact us (fr)"
    And the JSON node "data.settings.brand.button.translations[1].language" should be equal to 2
    And the JSON node "data.settings.brand.button.size" should be equal to the string "medium"
    And the JSON node "data.settings.brand.chat.begin_mode" should be equal to the string "form"
    And the JSON node "data.settings.brand.chat.waiting_timeout" should be equal to 40
    And the JSON node "data.settings.brand.chat.popup.style" should be equal to the string "agent_text_input"
    And the JSON node "data.settings.brand.chat.popup.translations[0].language" should be equal to 1
    And the JSON node "data.settings.brand.chat.popup.translations[0].title" should be equal to the string "Customer Support (edited)"
    And the JSON node "data.settings.brand.chat.popup.translations[0].message" should be equal to the string "Need help? Just reply to start a live chat with one of our team. (edited)"
    And the JSON node "data.settings.brand.chat.popup.translations[0].heading" should be equal to the string "Ask us a question! (edited)"
    And the JSON node "data.settings.brand.chat.popup.translations[0].subheading" should be equal to the string "Our team are online and ready to help with your enquiries. Send us a message to get started. (edited)"
    And the JSON node "data.settings.brand.chat.popup.translations[1].language" should be equal to 2
    And the JSON node "data.settings.brand.chat.popup.translations[1].title" should be equal to the string "Customer Support (fr)"
    And the JSON node "data.settings.brand.chat.popup.translations[1].message" should be equal to the string "Need help? Just reply to start a live chat with one of our team. (fr)"
    And the JSON node "data.settings.brand.chat.popup.translations[1].heading" should be equal to the string "Ask us a question! (fr)"
    And the JSON node "data.settings.brand.chat.popup.translations[1].subheading" should be equal to the string "Our team are online and ready to help with your enquiries. Send us a message to get started. (fr)"
    And the JSON node "data.settings.brand.chat.custom_fields" should have 2 elements
    And the JSON node "data.settings.brand.chat.custom_fields[0].id" should be equal to "{cf1}"
    And the JSON node "data.settings.brand.chat.custom_fields[0].display_order" should be equal to 10
    And the JSON node "data.settings.brand.chat.custom_fields[0].is_enabled" should be equal to 1
    And the JSON node "data.settings.brand.chat.custom_fields[1].id" should be equal to "{cf2}"
    And the JSON node "data.settings.brand.chat.custom_fields[1].display_order" should be equal to 20
    And the JSON node "data.settings.brand.chat.custom_fields[1].is_enabled" should be equal to 0
    And the JSON node "data.settings.brand.chat.user_groups" should have 2 elements
    And the JSON node "data.settings.brand.chat.user_groups[0]" should be equal to 1
    And the JSON node "data.settings.brand.chat.user_groups[1]" should be equal to 2
    And the JSON node "data.settings.brand.ticket.select_department" should be equal to the string "custom"

  Scenario: I apply chat widget to the portal
    When I send a POST request to "/api/v2/settings/brands/{defaultBrandId}/widget/setup" with body:
    """
{
  "enabled_on_portal": true,
  "settings": {
    "global": {
      "chat": {
        "enabled": false,
        "email_validation": false,
        "require_login": false
      }
    },
    "brand": {
      "widget": {
        "type": "column",
        "position": "right",
        "agent_polling_timeout": 400,
        "enabled": true
      },
      "button": {
        "translations": [
          {
            "language": 1,
            "name": "Help (edited)",
            "contact_us": "Contact us (edited)"
          }
        ],
        "size": "large"
      },
      "chat": {
        "begin_mode": "conversation",
        "waiting_timeout": 40,
        "select_department": "custom",
        "popup": {
          "translations": [
            {
              "language": 1,
              "title": "Customer Support (edited)",
              "message": "Need help? Just reply to start a live chat with one of our team. (edited)",
              "heading": "Ask us a question! (edited)",
              "subheading": "Our team are online and ready to help with your enquiries. Send us a message to get started. (edited)"
            }
          ],
          "style": "agent_text_input"
        }
      },
      "ticket": {
        "select_department": "default",
        "default_department": 2
      }
    }
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/settings/brands/{defaultBrandId}/widget/setup"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.settings.global.chat.enabled" should be equal to 0
    And the JSON node "data.settings.global.chat.require_login" should be equal to 0
    And the JSON node "data.settings.global.chat.email_validation" should be equal to 0
    And the JSON node "data.enabled_on_portal" should be equal to 1

    And the JSON node "data.settings.brand.widget.type" should be equal to the string "column"
    And the JSON node "data.settings.brand.widget.enabled" should be equal to true
    And the JSON node "data.settings.brand.widget.position" should be equal to the string "right"
    And the JSON node "data.settings.brand.widget.agent_polling_timeout" should be equal to 400
    And the JSON node "data.settings.brand.button.translations[0].name" should be equal to the string "Help (edited)"
    And the JSON node "data.settings.brand.button.translations[0].contact_us" should be equal to the string "Contact us (edited)"
    And the JSON node "data.settings.brand.button.size" should be equal to the string "large"
    And the JSON node "data.settings.brand.chat.begin_mode" should be equal to the string "conversation"
    And the JSON node "data.settings.brand.chat.waiting_timeout" should be equal to 40
    And the JSON node "data.settings.brand.chat.popup.style" should be equal to the string "agent_text_input"
    And the JSON node "data.settings.brand.ticket.select_department" should be equal to the string "default"
    And the JSON node "data.settings.brand.ticket.default_department" should be equal to 2
