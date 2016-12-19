@new
Feature: Widget configuration

  Background:
    Given I have only default brand

  Scenario: I get widget options
    When I send a GET request to "/portal/api/widget/brand_options"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.widget" should exist
    And the JSON node "data.widget.type" should exist
    And the JSON node "data.widget.position" should exist
    And the JSON node "data.widget.agent_polling_timeout" should exist

    And the JSON node "data.button" should exist
    And the JSON node "data.button.translations" should exist
    And the JSON node "data.button.size" should exist
    And the JSON node "data.button.colors" should exist
    And the JSON node "data.button.colors" should exist
    And the JSON node "data.button.colors.background" should exist
    And the JSON node "data.button.colors.text" should exist

    And the JSON node "data.chat" should exist
    And the JSON node "data.chat.request_user_info" should exist
    And the JSON node "data.chat.begin_mode" should exist
    And the JSON node "data.chat.proactive" should exist
    And the JSON node "data.chat.proactive" should exist
    And the JSON node "data.chat.popup" should exist
    And the JSON node "data.chat.popup.translations" should exist
    And the JSON node "data.chat.popup.style" should exist
    And the JSON node "data.chat.waiting_timeout" should exist

    And the JSON node "data.ticket" should exist
    And the JSON node "data.ticket.select_department" should exist
    And the JSON node "data.ticket.default_department" should exist
