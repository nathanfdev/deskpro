@new
Feature: Widget Chat
  Chat department selection

  Background:
    Given no Person records exist
    And I have only default brand
    # This is needed because it will enforce usergroups creation
    And a user with "user@deskpro.dev" email exists
    And I have guest portal api session with code "AAAAAAAAAAAAAAA"
    And only the following "Department" records exist:
      | #   | title   | is_chat_enabled | parent | brands           |
      | d1  | Dep1    | 1               |        | [{defaultBrand}] |
      | cd1 | SubDep1 | 1               | {d1}   | [{defaultBrand}] |
      | cd2 | SubDep2 | 1               | {d1}   | [{defaultBrand}] |
      | d2  | Dep2    | 1               |        | [{defaultBrand}] |
    And I grant the "{d1}" department permission of chat app for usergroup everyone
    And I grant the "{cd1}" department permission of chat app for usergroup everyone
    And I grant the "{cd2}" department permission of chat app for usergroup everyone
    And I grant the "{d2}" department permission of chat app for usergroup everyone
    And the setting "portal.chat.email_validation" is set to 0
    And the setting "portal.chat.require_login" is set to 0

  Scenario: Check leaf department
    # This need to be done because otherwise there wouldn't be request in request stack
    Given I send a GET request to "/"
    And "requestUserInfo" widget brand chat setting is set to 1 for "defaultBrand"
    When I send a POST request to "/portal/api/chats/create?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key             | value |
      | chat_department | {d1}  |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.chat_department.errors[0].code" should be equal to "not_assignable_department"

  Scenario: Check bad department
     # This need to be done because otherwise there wouldn't be request in request stack
    Given I send a GET request to "/"
    And "requestUserInfo" widget brand chat setting is set to 1 for "defaultBrand"
    When I send a POST request to "/portal/api/chats/create?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key             | value |
      | chat_department | 404404  |
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "fields.chat_department.errors[0].code" should be equal to "bad_choice"

  Scenario: Check valid department
     # This need to be done because otherwise there wouldn't be request in request stack
    Given I send a GET request to "/"
    And "requestUserInfo" widget brand chat setting is set to 1 for "defaultBrand"
    When I send a POST request to "/portal/api/chats/create?dpsid={sid_AAAAAAAAAAAAAAA}" with parameters:
      | key             | value |
      | chat_department | {cd1}  |
    Then the response status code should be 200
    And the response should be in JSON
