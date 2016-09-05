@new
Feature: /person_custom_fields endpoint
  To retrieve DeskPRO person custom fields
  As an API user
  I want an API endpoint

  Background:
    Given I'm authenticated as "admin"

  Scenario: I retrieve a list of custom fields
    Given only the following "CustomDefPerson" records exist:
      | #     | parent | title       | description         | is_enabled | is_user_enabled | type          |
      | cfp1  |        | Field1      | Field with children | 1          | 1               | single_choice |
      | cfpc1 | {cfp1} | ChildField1 | Child for Field1    | 1          | 1               |               |
      | cfpc2 | {cfp1} | ChildField2 | Child for Field1    | 1          | 1               |               |
      | cfpc3 | {cfp1} | ChildField3 | Child for Field1    | 1          | 1               |               |
      | cfp2  |        | Field2      | Text field          | 1          | 1               | text          |
      | cfp3  |        | Field3      | Datetime field      | 1          | 1               | datetime      |

    When I send a GET request to "/api/v2/person_custom_fields?order_by=id"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{cfp1}"
    And the JSON node "data[0].widget_type" should be equal to "choice"
    And the JSON node "data[0].title" should be equal to "Field1"
    And the JSON node "data[0].description" should be equal to "Field with children"
    And the JSON node "data[0].parent" should be equal to 0
    And the JSON node "data[0].children" should not exist
    And the JSON node "data[0].choices" should have 3 elements
    And the JSON node "data[0].choices[0].id" should be equal to "{cfpc1}"
    And the JSON node "data[0].choices[1].id" should be equal to "{cfpc2}"
    And the JSON node "data[0].choices[2].id" should be equal to "{cfpc3}"

    And the JSON node "data[1].id" should be equal to "{cfp2}"
    And the JSON node "data[1].widget_type" should be equal to "text"
    And the JSON node "data[1].title" should be equal to "Field2"
    And the JSON node "data[1].description" should be equal to "Text field"
    And the JSON node "data[1].parent" should be equal to 0
    And the JSON node "data[1].choices" should have 0 elements

    And the JSON node "data[2].id" should be equal to "{cfp3}"
    And the JSON node "data[2].widget_type" should be equal to "datetime"
    And the JSON node "data[2].title" should be equal to "Field3"
    And the JSON node "data[2].description" should be equal to "Datetime field"
    And the JSON node "data[2].parent" should be equal to 0
    And the JSON node "data[2].choices" should have 0 elements

  Scenario: I get child field
    Given only the following "CustomDefPerson" records exist:
      | #     | parent | title       | description         | is_enabled | is_user_enabled | type          |
      | cfp1  |        | Field1      | Field with children | 1          | 1               | single_choice |
      | cfpc1 | {cfp1} | ChildField1 | Child for Field1    | 1          | 1               |               |
    When I send a GET request to "/api/v2/person_custom_fields/{cfpc1}"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.id" should be equal to "{cfpc1}"
    And the JSON node "data.title" should be equal to "ChildField1"
    And the JSON node "data.description" should be equal to 0
    And the JSON node "data.parent" should be equal to "{cfp1}"
    And the JSON node "data.choices" should have 0 elements
