@new
Feature: /people endpoint with external unique key custom field

  Background:
    Given there are no "Person" records
    And only the following "CustomDefPerson" records exist:
      | #     | parent | title       | description         | is_enabled | is_user_enabled | type                |
      | cfp1  |        | EUK         | External Unique Key | 1          | 1               | external_unique_key |
    And there are no "Usergroup" records
    And I'm authenticated as "admin"

  Scenario: I create a person without an external unique key field while it exists
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "Sample Person",
  "password": "password",
  "primary_email": "sample.person@deskpro.com"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~cfp1~.errors[0].code" should be equal to "required"

  Scenario: I create a person with duplicate value for an external unique key field
    Given only the following "CustomDefPerson" records exist:
      | #     | parent | title       | description         | is_enabled | is_user_enabled | type                |
      | cfp1  |        | EUK         | External Unique Key | 1          | 1               | external_unique_key |
    And the object "admin" has "cfp1" custom data set to "test"
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "Sample Person",
  "password": "password",
  "primary_email": "sample.person@deskpro.com",
  "fields": {
    "~cfp1~": "test"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~cfp1~.errors[0].code" should be equal to "dupe_unique_key"

  Scenario: I update a person with duplicate value for an external unique key field
    Given only the following "CustomDefPerson" records exist:
      | #     | parent | title       | description         | is_enabled | is_user_enabled | type                |
      | cfp1  |        | EUK         | External Unique Key | 1          | 1               | external_unique_key |
    And "agent" person exists
    And the object "agent" has "cfp1" custom data set to "test"
    When I send a PUT request to "/api/v2/people/~admin~" with body:
    """
{
  "fields": {
    "~cfp1~": "test"
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.fields.fields.fields_~cfp1~.errors[0].code" should be equal to "dupe_unique_key"

  Scenario: I create a person with correct external unique key field while it exists
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "Sample Person",
  "password": "password",
  "primary_email": "sample.person@deskpro.com",
  "fields": {
    "~cfp1~": "test"
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.fields.{cfp1}.value" should be equal to "test"
