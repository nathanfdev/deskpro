@new
Feature: /organizations endpoint
  To CRUD DeskPRO organizations
  As an API user
  I want an API endpoint

  Background:
    Given no Person records exist
    And I'm authenticated as admin
    And there are no Blob records in the DB
    And I add the following Usergroup records:
      | #  | Sys name     | Title    |
      | g1 | demo_group_1 | Group #1 |
      | g2 | demo_group_2 | Group #2 |
    And only the following Organization records exist:
      | #         | Name                  |
      | microsoft | Microsoft Corporation |
      | yahoo     | Yahoo Inc             |
    And only the following custom organization fields exist:
      | #          | Type | Title      |
      | text_field | text | Text field |

  Scenario: I retrieve list of organizations
    When I send a GET request to "/api/v2/organizations"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements

  Scenario: I try to add a new organization with empty request
    When I send a POST request to "/api/v2/organizations"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors.fields.name.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.name.errors[0].message" should exist

  Scenario: I try to add a new organization with not valid email domain
    When I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "Yahoo",
  "email_domains": [{"title": "domain1.com"}, "domain2.com"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.email_domains.fields.email_domains_0.errors[0].code" should be equal to "invalid_data_type"

  Scenario: I try to add a new organization with duplicate email domains
    When I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "Yahoo",
  "email_domains": ["domain1.com", "domain2.com", "domain2.com"]
}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors.fields.email_domains.errors[0].code" should be equal to "not_unique_collection"
    And the JSON node "errors.fields.email_domains.errors[0].message" should be equal to "One or more of the given values is not unique."

  Scenario: I check org email domain non empty validation
    When I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "Yahoo",
  "email_domains": ["", "domain2.com"]
}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors.fields.email_domains.fields.email_domains_0.errors[0].code" should be equal to "required"

  Scenario: I create a new organization
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    When I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "Ebay",
  "summary": "Ebay Inc.",
  "picture_blob": "AAAAAAAAAAAAAAAAAA",
  "labels": ["label1", "label2"],
  "email_domains": ["domain1.com", "domain2.com"],
  "user_groups": [~g1~, ~g2~],
  "contact_data": {
    "website": [
      {"url": "http://ebay.com"}
    ],
    "facebook": [
      {"url": "http://facebook.com/profile"}
    ],
    "twitter": [
      {
        "username": "twitter_username",
        "comment": "some text"
      }
    ],
    "linked_in": [
      {"url": "http://linkedin.com/in/profile"}
    ],
    "instant_message": [
      {
        "username": "aim_user",
        "service": "aim"
      },
      {
        "username": "skype_user",
        "service": "skype"
      }
    ],
    "phone": [
      {
        "type": "mobile",
        "code": "+1",
        "number": "234-534-5345"
      }
    ],
    "address": [
      {
        "address": "address",
        "city": "city",
        "state": "state",
        "zip": "zip",
        "country": "GB"
      }
    ]
  }
}
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON node "data.id" should exist
    And the JSON node "data.name" should be equal to "Ebay"
    And the JSON node "data.summary" should be equal to "Ebay Inc."
    And the JSON node "data.importance" should be equal to 0
    And the JSON node "data.labels" should have 2 elements
    And the JSON node "data.labels[0]" should be equal to "label1"
    And the JSON node "data.labels[1]" should be equal to "label2"
    And the JSON node "data.email_domains" should have 2 elements
    And the JSON node "data.email_domains[0]" should be equal to "domain1.com"
    And the JSON node "data.email_domains[1]" should be equal to "domain2.com"
    And the JSON node "data.user_groups" should have 2 elements
    And the JSON node "data.contact_data" should have 8 elements
    And the JSON node "data.contact_data[0].id" should exist
    And the JSON node "data.contact_data[0].contact_type" should be equal to "phone"
    And the JSON node "data.contact_data[1].id" should exist
    And the JSON node "data.contact_data[1].contact_type" should be equal to "website"
    And the JSON node "data.contact_data[2].id" should exist

    When I send a GET request to "/api/v2/organizations/{lastCreatedId}/contact_data"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 8 elements

    And the JSON node "data[0].contact_type" should be equal to "phone"
    And the JSON node "data[0].code" should be equal to "+1"
    And the JSON node "data[0].number" should be equal to "234-534-5345"
    And the JSON node "data[0].type" should be equal to "mobile"
    And the JSON node "data[0].comment" should be equal to 0

    And the JSON node "data[1].contact_type" should be equal to "website"
    And the JSON node "data[1].url" should contain "ebay.com"
    And the JSON node "data[1].comment" should be equal to 0

    And the JSON node "data[2].contact_type" should be equal to "instant_message"
    And the JSON node "data[2].username" should be equal to "aim_user"
    And the JSON node "data[2].service" should be equal to "aim"
    And the JSON node "data[2].comment" should be equal to 0

    And the JSON node "data[3].contact_type" should be equal to "instant_message"
    And the JSON node "data[3].username" should be equal to "skype_user"
    And the JSON node "data[3].service" should be equal to "skype"
    And the JSON node "data[3].comment" should be equal to 0

    And the JSON node "data[4].contact_type" should be equal to "twitter"
    And the JSON node "data[4].username" should be equal to "twitter_username"
    And the JSON node "data[4].comment" should be equal to "some text"

    And the JSON node "data[5].contact_type" should be equal to "linked_in"
    And the JSON node "data[5].url" should contain "linkedin.com"
    And the JSON node "data[5].url" should contain "profile"
    And the JSON node "data[5].comment" should be equal to 0

    And the JSON node "data[6].contact_type" should be equal to "facebook"
    And the JSON node "data[6].url" should contain "facebook.com"
    And the JSON node "data[6].url" should contain "profile"
    And the JSON node "data[6].comment" should be equal to 0

    And the JSON node "data[7].contact_type" should be equal to "address"
    And the JSON node "data[7].address" should be equal to "address"
    And the JSON node "data[7].city" should be equal to "city"
    And the JSON node "data[7].state" should be equal to "state"
    And the JSON node "data[7].zip" should be equal to "zip"
    And the JSON node "data[7].country" should be equal to "GB"
    And the JSON node "data[7].comment" should be equal to 0

  Scenario: I update an organization
    Given I create blob with auth code "BBBBBBBBBBBBBBBBBB"
    When I send a PUT request to "/api/v2/organizations/{yahoo}" with body:
    """
{
  "name": "Yahoo Inc.",
  "summary": "Yahoo Inc. organization",
  "importance": 5,
  "picture_blob": "BBBBBBBBBBBBBBBBBB",
  "parent": ~microsoft~,
  "labels": ["label1", "label3"],
  "email_domains": ["domain1.com", "domain3.com"],
  "fields": {
    "~text_field~": "some text"
  },
  "contact_data": {
    "twitter": [
      {
        "username": "changed_twitter_username"
      }
    ]
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/organizations/{yahoo}"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.name" should be equal to "Yahoo Inc."
    And the JSON node "data.summary" should be equal to "Yahoo Inc. organization"
    And the JSON node "data.importance" should be equal to 5
    And the JSON node "data.parent" should be equal to "{microsoft}"
    And the JSON node "data.labels" should have 2 elements
    And the JSON node "data.labels[0]" should be equal to "label1"
    And the JSON node "data.labels[1]" should be equal to "label3"
    And the JSON node "data.email_domains" should have 2 elements
    And the JSON node "data.email_domains[0]" should be equal to "domain1.com"
    And the JSON node "data.email_domains[1]" should be equal to "domain3.com"
    And the JSON node "data.fields" should have 1 element
    And the JSON node "data.fields.{text_field}.value" should be equal to "some text"
    And the JSON node "data.contact_data" should have 1 element

  Scenario: I try to create an organization with existing email domain
    Given I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "Test Organization",
  "email_domains": ["domain1.com"]
}
    """
    When I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "Another Test Organization",
  "email_domains": ["domain1.com", "domain2.com"]
}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors.fields.email_domains.fields.email_domains_0.errors[0].code" should be equal to "unique_entity"
    And the JSON node "errors.fields.email_domains.fields.email_domains_0.errors[0].message" should be equal to "This value already exists in the system."

  Scenario: I reset email domains
    When I send a PUT request to "/api/v2/organizations/{yahoo}" with body:
    """
{
  "email_domains": []
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/organizations/{yahoo}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data.email_domains" should have 0 elements

  Scenario: I filter by user_groups
    Given I send a PUT request to "/api/v2/organizations/{yahoo}" with body:
    """
    {
      "user_groups": [~g1~, ~g2~]
    }
    """
    Then the response status code should be 204
    When I send a GET request to "/api/v2/organizations"
    Then the JSON node "data" should have 2 elements

    When I send a GET request to "/api/v2/organizations?user_group[]={g1}"
    Then the JSON node "data" should have 1 element

  Scenario: I filter by labels
    Given I send a PUT request to "/api/v2/organizations/{yahoo}" with body:
    """
    {
      "labels": ["label1", "label3"]
    }
    """
    When I send a GET request to "/api/v2/organizations?label[]=label1&label[]=label3"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[0].labels[1]" should be equal to "label3"

    When I send a GET request to "/api/v2/organizations?label[]=label1"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[0].labels[1]" should be equal to "label3"

    When I send a GET request to "/api/v2/organizations?label[]=label1&label[]=label3&labels_mode=all"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[0].labels[1]" should be equal to "label3"

    When I send a GET request to "/api/v2/organizations?label[]=label1&label[]=label4"
    Then the JSON node "data" should have 1 element
    And the JSON node "data[0].labels" should have 2 elements
    And the JSON node "data[0].labels[0]" should be equal to "label1"
    And the JSON node "data[0].labels[1]" should be equal to "label3"

    When I send a GET request to "/api/v2/organizations?label[]=label1&label[]=label4&labels_mode=all"
    Then the JSON node "data" should have 0 element

    When I send a GET request to "/api/v2/organizations?no_labels=1"
    Then the JSON node "data" should have 1 element

  Scenario: I set org members
    Given "user1@deskpro.dev" user exists
    And "user2@deskpro.dev" user exists
    And "user3@deskpro.dev" user exists
    And I send a PUT request to "/api/v2/organizations/{yahoo}" with body:
    """
    {
      "members": [~user1@deskpro.dev~, ~user2@deskpro.dev~, ~user3@deskpro.dev~]
    }
    """

    When I send a GET request to "/api/v2/organizations/{yahoo}/members?order_by=id&order_dir=asc"
    Then the JSON node "data" should have 3 elements
    And the JSON node "data[0].id" should be equal to "{user1@deskpro.dev}"
    And the JSON node "data[1].id" should be equal to "{user2@deskpro.dev}"
    And the JSON node "data[2].id" should be equal to "{user3@deskpro.dev}"

  Scenario: I update org members
    Given only the following Organization records exist:
      | #  | Name |
      | o1 | Org  |
    And only the following User records exist:
      | #  | Email             | Organization |
      | u1 | user1@deskpro.dev | {o1}         |
      | u2 | user2@deskpro.dev | {o1}         |
      | u3 | user3@deskpro.dev | {o1}         |
      | u4 | user4@deskpro.dev | NULL         |
    And I send a PUT request to "/api/v2/organizations/{o1}" with body:
    """
    {
      "members": [~u2~, ~u4~]
    }
    """

    When I send a GET request to "/api/v2/organizations/{o1}/members?order_by=id&order_dir=asc"
    Then the JSON node "data" should have 2 elements
    And the JSON node "data[0].id" should be equal to "{u2}"
    And the JSON node "data[1].id" should be equal to "{u4}"

  Scenario: I set phone number country code w/o plus char
    When I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "Ebay",
  "contact_data": {
    "phone": [
      {
        "type": "mobile",
        "code": "1",
        "number": "234-534-5345"
      }
    ]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.contact_data[0].contact_type" should be equal to "phone"
    And the JSON node "data.contact_data[0].code" should be equal to "+1"
    And the JSON node "data.contact_data[0].number" should be equal to "234-534-5345"

  Scenario Outline: I set country by name
    When I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "Ebay",
  "contact_data": {
    "address": [
      {
        "address": "address",
        "city": "city",
        "state": "state",
        "zip": "zip",
        "country": "<country>"
      }
    ]
  }
}
    """
    Then the response status code should be 201
    And the JSON node "data.contact_data[0].country" should be equal to "<country>"

    Examples:
      | country        |
      | United Kingdom |
      | United States  |
