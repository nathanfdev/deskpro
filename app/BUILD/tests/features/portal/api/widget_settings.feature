Feature: Widget Settings

  Background: Fresh database
    Given I install the fresh data set

  Scenario: I get settings
    Given the setting "core.site_name" is set to "My helpdesk"
    When I send a GET request to "/portal/api/widget/settings"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.company.name" should be equal to "My helpdesk"

  Scenario Outline: I change chat settings
    Given the setting "portal.chat.email_validation" is set to <email_validation>
    Given the setting "portal.chat.require_login" is set to <require_login>
    When I send a GET request to "/portal/api/widget/settings"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "data.chat.email_validation" should be equal to "<email_validation>"
    And the JSON node "data.chat.require_login" should be equal to "<require_login>"

    Examples:
      | email_validation | require_login |
      | 0                | 0             |
      | 0                | 1             |
      | 1                | 0             |
      | 1                | 1             |
