@new
Feature: /ticket_forms
  I want to check custom fields

  Background:
    Given no Person records exist
    And I'm authenticated as admin
    And only the following Ticket records exist:
      | #  | Subject  |
      | t1 | Ticket 1 |

  Scenario: I check ticket fields
    Given only the following custom ticket fields exist:
      | #  | Type | Title      |
      | f1 | text | Text field |
      | f2 | text | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout      |
      | ticket_field_{f1} |
      | ticket_field_{f2} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f1~": "some text"
  }
}
    """
    Then the response status code should be 204
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "fields": {
    "~f2~": "another text"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    And the JSON node "data.fields.{f1}.value" should be equal to the string "some text"
    And the JSON node "data.fields.{f2}.value" should be equal to the string "another text"

  Scenario: I check own user fields
    Given only the following custom person fields exist:
      | #  | Type | Title      |
      | f1 | text | Text field |
      | f2 | text | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout    |
      | user_field_{f1} |
      | user_field_{f2} |

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "user_fields": {
    "~f1~": "some text"
  }
}
    """
    Then the response status code should be 204
    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "user_fields": {
    "~f2~": "another text"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the response status code should be 200
    And the JSON node "data.fields.{f1}.value" should be equal to the string "some text"
    And the JSON node "data.fields.{f2}.value" should be equal to the string "another text"

  Scenario: I check that custom fields are set for proper user
    Given only the following custom person fields exist:
      | #  | Type | Title      |
      | f1 | text | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout    |
      | user_field_{f1} |
    And "user_1@deskpro.dev" user exists
    And the "{t1}" record "person" prop is equal to "{user_1@deskpro.dev}"

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "user_fields": {
    "~f1~": "some text"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{admin}"
    Then the response status code should be 200
    And the JSON node "data.fields" should have 0 elements

    When I send a GET request to "/api/v2/people/{user_1@deskpro.dev}"
    Then the response status code should be 200
    And the JSON node "data.fields.{f1}.value" should be equal to the string "some text"

  Scenario: I check user fields if person was changed on submit
    Given only the following custom person fields exist:
      | #  | Type | Title      |
      | f1 | text | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout    |
      | user_field_{f1} |
    And "user_1@deskpro.dev" user exists
    And "user_2@deskpro.dev" user exists
    And the object "user_1@deskpro.dev" has "f1" custom data set to "some text"
    And the "{t1}" record "person" prop is equal to "{user_1@deskpro.dev}"

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": "user_2@deskpro.dev",
  "user_fields": {
    "~f1~": "changed text"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/people/{user_1@deskpro.dev}"
    Then the response status code should be 200
    And the JSON node "data.fields.{f1}.value" should be equal to the string "some text"

    When I send a GET request to "/api/v2/people/{user_2@deskpro.dev}"
    Then the response status code should be 200
    And the JSON node "data.fields.{f1}.value" should be equal to the string "changed text"

  Scenario: I check organization fields if ticket has no org yet
    Given only the following custom organization fields exist:
      | #  | Type | Title      |
      | f1 | text | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout   |
      | org_field_{f1} |
    And the following Organization records exist:
      | #  | Name  |
      | o1 | Org 1 |
    And the following User records exist:
      | #  | Name     | Organization |
      | p1 | Person 1 | {o1}         |
    And the "{t1}" record "person" prop is equal to "{p1}"

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "organization_fields": {
    "~f1~": "some text"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/organizations/{o1}"
    Then the response status code should be 200
    And the JSON node "data.fields.{f1}.value" should be equal to the string "some text"

  Scenario: I check organization fields if ticket and person in same org
    Given only the following custom organization fields exist:
      | #  | Type | Title      |
      | f1 | text | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout   |
      | org_field_{f1} |
    And the following Organization records exist:
      | #  | Name  |
      | o1 | Org 1 |
    And the following User records exist:
      | #  | Name     | Organization |
      | p1 | Person 1 | {o1}         |
    And the "{t1}" record "organization" prop is equal to "{o1}"
    And the "{p1}" record "organization" prop is equal to "{o1}"

    When I send a PUT request to "/api/v2/ticket_forms/agent/{t1}" with body:
    """
{
  "person": ~p1~,
  "organization_fields": {
    "~f1~": "some text"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/organizations/{o1}"
    Then the response status code should be 200
    And the JSON node "data.fields.{f1}.value" should be equal to the string "some text"
