@new
Feature: /ticket_forms validation
  I want to check participants validation

  Background:
    Given I'm authenticated as admin
    And agent and user exist
    And the only default ticket layout exists with fields:
      | agent_layout |
      | cc           |
      | followers    |

  Scenario: I sent not valid cc email data
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "cc": [
    {"cc": 1}
  ]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.cc.errors" should not exist
    And the JSON node "errors.fields.cc.fields" should have 1 element
    And the JSON node "errors.fields.cc.fields.cc_0.errors" should have 1 element
    And the JSON node "errors.fields.cc.fields.cc_0.errors[0].code" should be equal to "extra_fields"
    And the JSON node "errors.fields.cc.fields.cc_0.errors[0].message" should contain "cc"

  Scenario: I sent not valid cc email
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "cc": ["not_valid_email"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.cc.errors" should not exist
    And the JSON node "errors.fields.cc.fields" should have 1 element
    And the JSON node "errors.fields.cc.fields.cc_0.errors[0].code" should be equal to "person_not_found"
    And the JSON node "errors.fields.cc.fields.cc_0.errors[0].message" should contain "not_valid_email"

  Scenario: I confused participant fields
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "cc": ["agent@deskpro.dev"],
  "followers": ["user@deskpro.dev"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.cc.errors" should not exist
    And the JSON node "errors.fields.cc.fields" should have 1 element
    And the JSON node "errors.fields.cc.fields.cc_0.errors[0].code" should be equal to "person_not_user"
    And the JSON node "errors.fields.cc.fields.cc_0.errors[0].message" should contain "agent@deskpro.dev"
    And the JSON node "errors.fields.followers.fields" should have 1 element
    And the JSON node "errors.fields.followers.fields.followers_0.errors[0].code" should be equal to "person_not_agent"
    And the JSON node "errors.fields.followers.fields.followers_0.errors[0].message" should contain "user@deskpro.dev"
