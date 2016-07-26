@new
Feature: /organization_custom_fields endpoint
  To retrieve DeskPRO organization custom fields
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"

  Scenario: I retrieve a list of custom fields
    Given only the following "CustomDefOrganization" records exist:
      | #      | parent | title              | description  | handler_class                                     | is_user_enabled | is_enabled |
      | cdo1   |        | custom def 1       | desc 1       | "Application\DeskPRO\CustomFields\Handler\Choice" | 1               | 1          |
      | cdoc11 | {cdo1} | custom def child 1 | desc child 1 |                                                   | 1               | 1          |
      | cdoc12 | {cdo1} | custom def child 2 | desc child 2 |                                                   | 1               | 1          |
      | cdoc13 | {cdo1} | custom def child 3 | desc child 3 |                                                   | 1               | 1          |
      | cdo2   |        | custom def 2       | desc 2       | Application\DeskPRO\CustomFields\Handler\Text     | 1               | 1          |
      | cdo3   |        | custom def 3       | desc 3       | Application\DeskPRO\CustomFields\Handler\DateTime | 1               | 1          |
    When I send a GET request to "/api/v2/organization_custom_fields?order_by=id"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{cdo1}"
    And the JSON node "data[0].title" should be equal to "custom def 1"
    And the JSON node "data[0].description" should be equal to "desc 1"
    And the JSON node "data[0].parent" should be equal to 0
    And the JSON node "data[0].children" should not exist
    And the JSON node "data[0].choices" should have 3 elements
    And the JSON node "data[0].choices[0].id" should be equal to "{cdoc11}"
    And the JSON node "data[0].choices[1].id" should be equal to "{cdoc12}"
    And the JSON node "data[0].choices[2].id" should be equal to "{cdoc13}"

    And the JSON node "data[1].id" should be equal to "{cdo2}"
    And the JSON node "data[1].title" should be equal to "custom def 2"
    And the JSON node "data[1].description" should be equal to "desc 2"
    And the JSON node "data[1].parent" should be equal to 0
    And the JSON node "data[1].choices" should have 0 elements

    And the JSON node "data[2].id" should be equal to "{cdo3}"
    And the JSON node "data[2].title" should be equal to "custom def 3"
    And the JSON node "data[2].description" should be equal to "desc 3"
    And the JSON node "data[2].parent" should be equal to 0
    And the JSON node "data[2].choices" should have 0 elements

  Scenario: I get child field
    Given only the following "CustomDefOrganization" records exist:
      | #      | parent | title              | description  | handler_class                                     | is_user_enabled | is_enabled |
      | cdo1   |        | custom def 1       | desc 1       | "Application\DeskPRO\CustomFields\Handler\Choice" | 1               | 1          |
      | cdoc11 | {cdo1} | custom def child 1 | desc child 1 |                                                   | 1               | 1          |
      | cdoc12 | {cdo1} | custom def child 2 | desc child 2 |                                                   | 1               | 1          |
      | cdoc13 | {cdo1} | custom def child 3 | desc child 3 |                                                   | 1               | 1          |
    When I send a GET request to "/api/v2/organization_custom_fields/{cdoc11}"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.id" should be equal to "{cdoc11}"
    And the JSON node "data.title" should be equal to "custom def child 1"
    And the JSON node "data.description" should be equal to "desc child 1"
    And the JSON node "data.parent" should be equal to "{cdo1}"
    And the JSON node "data.choices" should have 0 elements
