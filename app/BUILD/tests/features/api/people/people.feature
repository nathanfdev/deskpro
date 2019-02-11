@new
Feature: /people endpoint
  To retrieve DeskPRO people
  As an API user
  I want an API endpoint

  Background:
    Given there are no "Person" records
    And there are no "Usergroup" records
    And I'm authenticated as "admin"

  Scenario: I get paginated list of people
    Given "user1@deskpro.dev" user exists
    Given "user2@deskpro.dev" user exists
    Given "user3@deskpro.dev" user exists
    When I send a GET request to "/api/v2/people"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "data[0].name" should exist

  Scenario: I get a single person
    When I send a GET request to "/api/v2/people/{me}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.id" should be equal to "{me}"

  Scenario: I try to create a person providing empty data
    When I send a POST request to "/api/v2/people"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.errors" should not exist
    And the JSON node "errors.fields.primary_email.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.emails.errors[0].code" should be equal to "too_few_elements"

  Scenario: I try to create a person providing empty name
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "primary_email": "sample.person@deskpro.com"
}
    """
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.errors" should not exist
    And the JSON node "errors.fields.name.errors[0].code" should be equal to "required"

  Scenario: I create a person
    Given only the following "Organization" records exist:
      | #  | name         |
      | o1 | Organization |
    And "knights" user group exists
    And "merchants" user group exists
    And "ice" agent group exists
    And "fire" agent group exists
    And only the following custom person fields exist:
      | #  | Type | Title      |
      | f1 | text | Text field |

    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "Sample Person",
  "password": "password",
  "primary_email": "sample.person@deskpro.com",
  "organization": ~o1~,
  "organization_position": "Chief Sample Person",
  "user_groups": [~knights_group~, ~merchants_group~],
  "agent_groups": [~ice_group~, ~fire_group~],
  "labels": ["label1", "label2"],
  "fields": {
    "~f1~": "some text"
  },
  "contact_data": {
    "website": [
      {"url": "http://site.com"}
    ],
    "facebook": [
      {"url": "http://facebook.com/profile"}
    ],
    "twitter": [
      {
        "username": "twitter_username",
        "comment": "some text"
      }
    ]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.name" should be equal to "Sample Person"
    And the JSON node "data.organization" should be equal to "{o1}"
    And the JSON node "data.organization_position" should be equal to "Chief Sample Person"
    And the JSON node "data.primary_email" should be equal to "sample.person@deskpro.com"
    And the JSON node "data.labels" should have 2 elements
    And the JSON node "data.labels[0]" should be equal to "label1"
    And the JSON node "data.labels[1]" should be equal to "label2"
    And the JSON node "data.fields.{f1}.value" should be equal to "some text"
    And the JSON node "data.user_groups" should have 2 elements
    And the JSON node "data.user_groups[0]" should be equal to "{knights_group}"
    And the JSON node "data.user_groups[1]" should be equal to "{merchants_group}"
    And the JSON node "data.agent_groups" should have 2 elements
    And the JSON node "data.agent_groups[0]" should be equal to "{ice_group}"
    And the JSON node "data.agent_groups[1]" should be equal to "{fire_group}"
    And the JSON node "data.contact_data[0].contact_type" should be equal to "website"
    And the JSON node "data.contact_data[1].contact_type" should be equal to "twitter"
    And the JSON node "data.contact_data[1].username" should be equal to "twitter_username"
    And the JSON node "data.contact_data[1].comment" should be equal to "some text"
    And the JSON node "data.contact_data[2].contact_type" should be equal to "facebook"
    And the JSON node "data.is_disabled" should be equal to 0

  Scenario: I modify and retrieve a person
    Given "guineapig@deskpro.dev" user exists
    And "knights" user group exists
    And "merchants" agent group exists
    When I send a PUT request to "/api/v2/people/{guineapig@deskpro.dev}" with body:
    """
{
  "name": "Modified Name",
  "user_groups": [~knights_group~],
  "agent_groups": [~merchants_group~]
}
    """
    And the response status code should be 204

    When I send a GET request to "/api/v2/people/{guineapig@deskpro.dev}"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "Modified Name"
    And the JSON node "data.user_groups" should have 1 element
    And the JSON node "data.user_groups[0]" should be equal to "{knights_group}"
    And the JSON node "data.agent_groups" should have 1 element
    And the JSON node "data.agent_groups[0]" should be equal to "{merchants_group}"

  Scenario: I modify user_groups
    Given "guineapig@deskpro.dev" user exists
    And "knights" user group exists
    And "merchants" agent group exists
    When I send a PUT request to "/api/v2/people/{guineapig@deskpro.dev}" with body:
    """
{
  "user_groups": [~knights_group~],
  "agent_groups": [~merchants_group~]
}
    """
    And the response status code should be 204

    When I send a GET request to "/api/v2/people/{guineapig@deskpro.dev}"
    Then the response status code should be 200
    And the JSON node "data.user_groups" should have 1 element
    And the JSON node "data.user_groups[0]" should be equal to "{knights_group}"
    And the JSON node "data.agent_groups" should have 1 element
    And the JSON node "data.agent_groups[0]" should be equal to "{merchants_group}"

  Scenario: I filter by user_groups
    Given "someuser@deskpro.dev" user exists
    And "someuser2@deskpro.dev" user exists
    And "someuser3@deskpro.dev" user exists
    And "someuser4@deskpro.dev" user exists
    And "group" user group exists
    And "group2" user group exists
    And "group3" user group exists
    And I add "someuser@deskpro.dev" usergroup relation "group"
    And I add "someuser2@deskpro.dev" usergroup relation "group"

    When I send a GET request to "/api/v2/people"
    Then the JSON node "data" should have 5 elements

    When I send a GET request to "/api/v2/people?user_group[]={group_group}&user_group[]={group2_group}&user_group[]={group3_group}"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].user_groups[0]" should be equal to "{group_group}"

  Scenario: I reset user_groups
    Given "guineapig@deskpro.dev" user exists
    And "knights" user group exists
    And "merchants" agent group exists
    And I add "guineapig@deskpro.dev" usergroup relation "knights"
    And I add "guineapig@deskpro.dev" usergroup relation "merchants"
    When I send a PUT request to "/api/v2/people/{guineapig@deskpro.dev}" with body:
    """
{
  "user_groups": [],
  "agent_groups": []
}
    """
    And the response status code should be 204

    When I send a GET request to "/api/v2/people/{guineapig@deskpro.dev}"
    Then the response status code should be 200
    And the JSON node "data.user_groups" should have 0 elements

  Scenario: I filter by labels
    Given "someuser@deskpro.dev" user exists
    And "someuser2@deskpro.dev" user exists
    And "someuser3@deskpro.dev" user exists
    And "someuser4@deskpro.dev" user exists
    And I mark "someuser@deskpro.dev" user with "label1" label
    And I mark "someuser2@deskpro.dev" user with "label1" label
    And I mark "someuser@deskpro.dev" user with "label2" label
    And I mark "someuser2@deskpro.dev" user with "label2" label
    And I mark "someuser4@deskpro.dev" user with "label4" label

    When I send a GET request to "/api/v2/people?label[]=label1&label[]=label2"
    Then the JSON node "data" should have 2 elements

    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[0].labels[1]" should be equal to "label2"

    When I send a GET request to "/api/v2/people?label[]=label1"
    Then the JSON node "data" should have 2 element
    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[0].labels[1]" should be equal to "label2"

    When I send a GET request to "/api/v2/people?label[]=label1&label[]=label2&labels_mode=all"
    Then the JSON node "data" should have 2 element
    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[0].labels[1]" should be equal to "label2"

    When I send a GET request to "/api/v2/people?label[]=label1&label[]=label4&order_by=id&order_dir=asc"
    Then the JSON node "data" should have 3 element

    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[2].labels[0]" should be equal to "label4"

    When I send a GET request to "/api/v2/people?label[]=label1&label[]=label4&labels_mode=all"
    Then the JSON node "data" should have 0 element

    When I send a GET request to "/api/v2/people?no_labels=1"
    Then the JSON node "data" should have 2 element

  Scenario: I clear session data for person
    Given "guineapig@deskpro.dev" user exists
    When I send a POST request to "/api/v2/people/{guineapig@deskpro.dev}/sessions/clear"
    Then the response status code should be 204

  Scenario: I set person as disabled
    Given "guineapig@deskpro.dev" user exists
    When I send a PUT request to "/api/v2/people/{guineapig@deskpro.dev}" with body:
    """
{
  "is_disabled": true
}
    """
    And the response status code should be 204

    When I send a GET request to "/api/v2/people/{guineapig@deskpro.dev}"
    Then the response status code should be 200
    And the JSON node "data.is_disabled" should be equal to 1
