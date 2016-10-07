@new
Feature: Widget Setup123

  Background:
    Given I'm authenticated as admin
    And I have only default brand

  Scenario: I send empty request
    When I send a POST request to "/api/v2/settings/brands/{defaultBrandId}/widget/setup"
    Then the response status code should be 400

    And the JSON node "errors.fields.brand.fields.widget.fields.type.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.brand.fields.widget.fields.type.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.brand.fields.widget.fields.position.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.brand.fields.widget.fields.position.errors[0].message" should be equal to "This value should not be blank."

    And the JSON node "errors.fields.brand.fields.button.fields.size.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.brand.fields.button.fields.size.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.brand.fields.button.fields.translations.errors[0].code" should be equal to "too_few_elements"
    And the JSON node "errors.fields.brand.fields.button.fields.translations.errors[0].message" should be equal to "This collection should contain 1 elements or more."

    And the JSON node "errors.fields.brand.fields.chat.fields.popup.fields.style.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.brand.fields.chat.fields.popup.fields.style.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.brand.fields.chat.fields.popup.fields.translations.errors[0].code" should be equal to "too_few_elements"
    And the JSON node "errors.fields.brand.fields.chat.fields.popup.fields.translations.errors[0].message" should be equal to "This collection should contain 1 elements or more."

    And the JSON node "errors.fields.brand.fields.ticket.fields.select_department.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.brand.fields.ticket.fields.select_department.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.brand.fields.ticket.fields.default_department.errors[0].code" should not exist

  Scenario: I check not valid values
    When I send a POST request to "/api/v2/settings/brands/{defaultBrandId}/widget/setup" with body:
    """
    {
      "brand": {
        "widget": {
          "type": "unknown",
          "position": "unknown",
          "agent_polling_timeout": 10
        },
        "button": {
          "size": "unknown"
        },
        "chat": {
          "waiting_timeout": 10,
          "popup": {
            "style": "unknown"
          }
        },
        "ticket": {
          "select_department": "unknown"
        }
      }
    }
    """
    Then the response status code should be 400

    And the JSON node "errors.fields.brand.fields.widget.fields.type.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.brand.fields.widget.fields.type.errors[0].message" should be equal to "One or more of the given values is invalid."
    And the JSON node "errors.fields.brand.fields.widget.fields.position.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.brand.fields.widget.fields.position.errors[0].message" should be equal to "One or more of the given values is invalid."
    And the JSON node "errors.fields.brand.fields.widget.fields.agent_polling_timeout.errors[0].code" should be equal to "too_low"
    And the JSON node "errors.fields.brand.fields.widget.fields.agent_polling_timeout.errors[0].message" should be equal to "This value should be greater than or equal to 300."

    And the JSON node "errors.fields.brand.fields.button.fields.size.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.brand.fields.button.fields.size.errors[0].message" should be equal to "One or more of the given values is invalid."

    And the JSON node "errors.fields.brand.fields.chat.fields.popup.fields.style.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.brand.fields.chat.fields.popup.fields.style.errors[0].message" should be equal to "One or more of the given values is invalid."
    And the JSON node "errors.fields.brand.fields.chat.fields.waiting_timeout.errors[0].code" should be equal to "too_low"
    And the JSON node "errors.fields.brand.fields.chat.fields.waiting_timeout.errors[0].message" should be equal to "This value should be greater than or equal to 30."

    And the JSON node "errors.fields.brand.fields.ticket.fields.select_department.errors[0].code" should be equal to "bad_choice"
    And the JSON node "errors.fields.brand.fields.ticket.fields.select_department.errors[0].message" should be equal to "One or more of the given values is invalid."

  Scenario: I check tickets default department
    When I send a POST request to "/api/v2/settings/brands/{defaultBrandId}/widget/setup" with body:
    """
{
  "brand": {
    "ticket": {
      "select_department": "default"
    }
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.brand.fields.ticket.fields.default_department.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.brand.fields.ticket.fields.default_department.errors[0].message" should be equal to "This value should not be blank."

    When I send a POST request to "/api/v2/settings/brands/{defaultBrandId}/widget/setup" with body:
    """
{
  "brand": {
    "ticket": {
      "select_department": "custom"
    }
  }
}
    """
    Then the JSON node "errors.fields.brand.fields.ticket.fields.default_department.errors[0].code" should not exist

  Scenario: I validate is numeric check
    When I send a POST request to "/api/v2/settings/brands/{defaultBrandId}/widget/setup" with body:
    """
{
  "brand": {
    "widget": {
      "agent_polling_timeout": "text"
    },
    "chat": {
      "waiting_timeout": "text"
    }
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.brand.fields.widget.fields.agent_polling_timeout.errors[0].code" should be equal to "numeric"
    And the JSON node "errors.fields.brand.fields.widget.fields.agent_polling_timeout.errors[0].message" should be equal to "Please enter a number, with no other characters."
    And the JSON node "errors.fields.brand.fields.chat.fields.waiting_timeout.errors[0].code" should be equal to "numeric"
    And the JSON node "errors.fields.brand.fields.chat.fields.waiting_timeout.errors[0].message" should be equal to "Please enter a number, with no other characters."

  Scenario: I validate duplicate translations
    When I send a POST request to "/api/v2/settings/brands/{defaultBrandId}/widget/setup" with body:
    """
{
  "brand": {
    "button": {
      "translations": [
        {
          "name": "Help"
        },
        {
          "name": "Help"
        }
      ]
    },
    "chat": {
      "popup": {
        "translations": [
          {
            "language": 2,
            "title": "Title",
            "message": "Message"
          },
          {
            "language": 2,
            "title": "Title",
            "message": "Message"
          }
        ]
      }
    }
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.brand.fields.button.fields.translations.errors[0].code" should be equal to "not_unique_collection"
    And the JSON node "errors.fields.brand.fields.button.fields.translations.errors[0].message" should be equal to "One or more of the given values is not unique."
    And the JSON node "errors.fields.brand.fields.button.fields.translations.fields.translations_0.fields.language.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.brand.fields.button.fields.translations.fields.translations_0.fields.language.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.brand.fields.chat.fields.popup.fields.translations.errors[0].code" should be equal to "not_unique_collection"
    And the JSON node "errors.fields.brand.fields.chat.fields.popup.fields.translations.errors[0].message" should be equal to "One or more of the given values is not unique."
