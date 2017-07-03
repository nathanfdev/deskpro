@new
Feature: /ticket_forms endpoint
  I want to check custom fields criteria based on org fields

  Background:
    Given I'm authenticated as admin
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And the following Organization records exist:
      | #  | Name  |
      | o1 | Org 1 |
    And a user with "user_1@deskpro.dev" email exists
    And the "user_1@deskpro.dev" record organization prop is equal to "{o1}"
    And only the following custom ticket fields exist:
      | #   | Type | Title      |
      | tf1 | text | Text field |
    And only the following custom organization fields exist:
      | #   | Type | Title      |
      | of1 | text | Text field |
    And the only default ticket layout exists with fields:
      | agent_layout       | agent_layout_options                                                                                                                                                                                                                                    |
      | ticket_field_{tf1} | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":{"version":1,"mode":"all","terms":[{"type":"CheckOrgField~of1~","op":"contains","options":{"value":"has ticket field","type_name":"text","field_id":"~of1~"}}]}} |
      | org_field_{of1}    | {"on_newticket": true, "on_viewticket_mode":"always", "on_editticket":true, "criteria":null}                                                                                                                                                            |

  Scenario: The ticket field is present on the form
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "My ticket",
  "department": ~d1~,
  "person": ~user_1@deskpro.dev~,
  "organization_fields": {
    "~of1~": "has ticket field"
  },
  "fields": {
    "~tf1~": "data"
  },
  "message": {
    "message": "My message"
  }
}
    """
    Then the response status code should be 201

  Scenario: The ticket field is not present on the form
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "My ticket",
  "department": ~d1~,
  "person": ~user_1@deskpro.dev~,
  "organization_fields": {
    "~of1~": "no ticket field"
  },
  "fields": {
    "~tf1~": "data"
  },
  "message": {
    "message": "My message"
  }
}
    """
    Then the JSON node "errors.errors[0].code" should be equal to the string "extra_fields"
    And the JSON node "errors.errors[0].message" should be equal to the string "Unexpected field names: fields"
