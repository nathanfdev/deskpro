Feature: Person profile

  Background:
    Given I install the api data set
    And my request is authenticated

  Scenario: I get profile
    When I send a GET request to "/api/v2/me/profile"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.name" should be equal to "Link Admin"
    And the JSON node "data.display_name" should be equal to 0
    And the JSON node "data.primary_email" should be equal to "admin@deskpro.dev"
    And the JSON node "data.emails[0]" should be equal to "admin@deskpro.dev"
    And the JSON node "data.phone" should be equal to 0
    And the JSON node "data.language_id" should be equal to 1
    And the JSON node "data.timezone" should be equal to "UTC"
    And the JSON node "data.avatar" should be equal to 0

  Scenario: I check validation
    When I send a PUT request to "/api/v2/me/profile" with body:
    """
{
  "name": "",
  "emails": []
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.name.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.name.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.emails.errors[0].code" should be equal to "too_few_elements"
    And the JSON node "errors.fields.emails.errors[0].message" should be equal to "This collection should contain 1 elements or more."

  Scenario: I update person profile
    When I send a PUT request to "/api/v2/me/profile" with body:
    """
{
  "name": "New Name",
  "display_name": "Display Name",
  "language_id": 2,
  "primary_email": "my_new_email@deskpro.dev",
  "emails": [
    "email1@deskpro.dev",
    "email2@deskpro.dev"
  ]
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/me/profile"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.name" should be equal to "New Name"
    And the JSON node "data.display_name" should be equal to "Display Name"
    And the JSON node "data.primary_email" should be equal to "my_new_email@deskpro.dev"
    And the JSON node "data.emails" should have 3 elements
    And the JSON node "data.emails[0]" should be equal to "email1@deskpro.dev"
    And the JSON node "data.emails[1]" should be equal to "email2@deskpro.dev"
    And the JSON node "data.emails[2]" should be equal to "my_new_email@deskpro.dev"
    And the JSON node "data.phone" should be equal to 0
    And the JSON node "data.language_id" should be equal to 2
    And the JSON node "data.timezone" should be equal to "UTC"
    And the JSON node "data.avatar" should be equal to 0

  Scenario: I update person profile with invalid emails
    When I send a PUT request to "/api/v2/me/profile" with body:
    """
{
  "name": "New Name",
  "display_name": "Display Name",
  "language_id": 2,
  "primary_email": "my_new_email@deskpro.dev",
  "emails": [
    "invalid",
    "!@£903284::"
  ]
}
    """
    Then the response status code should be 401
