@basic @chat-nav @tasks-nav @people
Feature: /people endpoint
  To retrieve DeskPRO people
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I get paginated list of people
    When I send a GET request to "/api/v2/people"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 4 elements
    And the JSON node "data[0].name" should exist

  Scenario: I get a single person
    When I send a GET request to "/api/v2/people/1"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.id" should be equal to "1"
    And the JSON node "data.name" should be equal to "Link Admin"

  Scenario: I create a person
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "Sample Person",
  "primary_email": "sample.person@deskpro.com",
  "organization": 1,
  "organization_position": "Chief Sample Person",
  "user_groups": [3, 5],
  "agent_groups": [7, 8],
  "labels": ["label1", "label1", "label2"],
  "fields": {
    "6": "some text"
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
    And the JSON node "data.id" should be equal to 5
    And the JSON node "data.name" should be equal to "Sample Person"
    And the JSON node "data.organization" should be equal to 1
    And the JSON node "data.organization_position" should be equal to "Chief Sample Person"
    And the JSON node "data.primary_email" should be equal to "sample.person@deskpro.com"
    And the JSON node "data.labels" should have 2 elements
    And the JSON node "data.labels[0]" should be equal to "label1"
    And the JSON node "data.labels[1]" should be equal to "label2"
    And the JSON node "data.fields" should have 4 elements
    And the JSON node "data.fields.5.value" should exist
    And the JSON node "data.fields.6.value" should be equal to "some text"
    And the JSON node "data.fields.7.value" should be equal to 0
    And the JSON node "data.user_groups" should have 2 elements
    And the JSON node "data.user_groups[0]" should be equal to 3
    And the JSON node "data.user_groups[1]" should be equal to 5
    And the JSON node "data.agent_groups" should have 2 elements
    And the JSON node "data.agent_groups[0]" should be equal to 7
    And the JSON node "data.agent_groups[1]" should be equal to 8
    And the JSON node "data.contact_data[0].contact_type" should be equal to "website"
    And the JSON node "data.contact_data[0].id" should be equal to 1
    And the JSON node "data.contact_data[1].id" should be equal to 2
    And the JSON node "data.contact_data[1].contact_type" should be equal to "twitter"
    And the JSON node "data.contact_data[1].username" should be equal to "twitter_username"
    And the JSON node "data.contact_data[1].comment" should be equal to "some text"
    And the JSON node "data.contact_data[2].contact_type" should be equal to "facebook"
    And the JSON node "data.contact_data[2].id" should be equal to 3

  Scenario: I try to create a person providing empty data
    When I send a POST request to "/api/v2/people"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.errors" should not exist
    And the JSON node "errors.fields.name.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.name.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.primary_email.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.primary_email.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.emails.errors[0].code" should be equal to "too_few_elements"
    And the JSON node "errors.fields.emails.errors[0].message" should be equal to "This collection should contain 1 elements or more."

  Scenario: I modify and retrieve a person
    When I send a PUT request to "/api/v2/people/5" with body:
    """
{
  "name": "Modified Name",
  "user_groups": [5],
  "agent_groups": [7]
}
    """
    And the response status code should be 204

    When I send a GET request to "/api/v2/people/5"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "Modified Name"
    And the JSON node "data.user_groups" should have 1 element
    And the JSON node "data.user_groups[0]" should be equal to 5
    And the JSON node "data.agent_groups" should have 1 element
    And the JSON node "data.agent_groups[0]" should be equal to 7

  Scenario: I modify user_groups
    When I send a PUT request to "/api/v2/people/5" with body:
    """
{
  "user_groups": [3],
  "agent_groups": [8]
}
    """
    And the response status code should be 204

    When I send a GET request to "/api/v2/people/5"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "Modified Name"
    And the JSON node "data.user_groups" should have 1 element
    And the JSON node "data.user_groups[0]" should be equal to 3
    And the JSON node "data.agent_groups" should have 1 element
    And the JSON node "data.agent_groups[0]" should be equal to 8

  Scenario: I filter by user_groups
    When I send a GET request to "/api/v2/people"
    Then the JSON node "data" should have 5 elements

    When I send a GET request to "/api/v2/people?user_group[]=3"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].user_groups[0]" should be equal to 3

  Scenario: I reset user_groups
    When I send a PUT request to "/api/v2/people/5" with body:
    """
{
  "user_groups": [],
  "agent_groups": []
}
    """
    And the response status code should be 204

    When I send a GET request to "/api/v2/people/5"
    Then the response status code should be 200
    And the JSON node "data.name" should be equal to "Modified Name"
    And the JSON node "data.user_groups" should have 0 elements

  Scenario: I filter by labels
    When I send a GET request to "/api/v2/people?label[]=label1&label[]=label2"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[0].labels[1]" should be equal to "label2"

    When I send a GET request to "/api/v2/people?label[]=label1"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[0].labels[1]" should be equal to "label2"

    When I send a GET request to "/api/v2/people?label[]=label1&label[]=label2&labels_mode=all"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[0].labels[1]" should be equal to "label2"

    When I send a GET request to "/api/v2/people?label[]=label1&label[]=label4"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[0].labels[1]" should be equal to "label2"

    When I send a GET request to "/api/v2/people?label[]=label1&label[]=label4&labels_mode=all"
    Then the JSON node "data" should have 0 element

    When I send a GET request to "/api/v2/people?no_labels=1"
    Then the JSON node "data" should have 4 element
