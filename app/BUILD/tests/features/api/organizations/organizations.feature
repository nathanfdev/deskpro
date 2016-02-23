Feature: /organizations endpoint
  To CRUD DeskPRO organizations
  As a developer
  I want an API endpoint

  Background:
    Given I install the api data set
    And my request is authenticated

  @reinstall
  Scenario: I retrieve list of organizations
    When I send a GET request to "/api/v2/organizations"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should have 2 elements

    And the JSON node "data[0].id" should be equal to 1
    And the JSON node "data[0].name" should be equal to "Organization 1"

    And the JSON node "data[1].id" should be equal to 2
    And the JSON node "data[1].name" should be equal to "Organization 2"

  Scenario: I try to add a new organization with empty request
    Given I create blob with auth code "AAAAAAAAAAAAAAAAAA"
    When I send a POST request to "/api/v2/organizations"
    Then the response status code should be 400
    And the response should be in JSON

    And the JSON node "errors.fields.name.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.name.errors[0].message" should be equal to "This value should not be blank."

    And the JSON node "errors.fields.importance.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.importance.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I try to add a new organization with not valid email domain
    When I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "Organization 3",
  "email_domains": [{"title": "domain1.com"}, "domain2.com"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.email_domains.fields.email_domains_0.errors[0].code" should be equal to "invalid_data_type"
    And the JSON node "errors.fields.email_domains.fields.email_domains_0.errors[0].message" should be equal to "This data type is not is data type that was expected."

  Scenario: I create a new organization
    When I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "Organization 3",
  "summary": "test organization",
  "importance": 3,
  "picture_blob": "AAAAAAAAAAAAAAAAAA",
  "labels": ["label 1", "label 2"],
  "email_domains": ["domain1.com", "domain2.com"]
}
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.name" should be equal to "Organization 3"
    And the JSON node "data.summary" should be equal to "test organization"
    And the JSON node "data.importance" should be equal to 3
    And the JSON node "data.labels" should have 2 elements
    And the JSON node "data.labels[0]" should be equal to "label 1"
    And the JSON node "data.labels[1]" should be equal to "label 2"
    And the JSON node "data.email_domains" should have 2 elements
    And the JSON node "data.email_domains[0]" should be equal to "domain1.com"
    And the JSON node "data.email_domains[1]" should be equal to "domain2.com"

  Scenario: I update an organization
    Given I create blob with auth code "BBBBBBBBBBBBBBBBBB"
    When I send a PUT request to "/api/v2/organizations/3" with body:
    """
{
  "name": "Updated organization 3",
  "summary": "updated test organization",
  "importance": 5,
  "picture_blob": "BBBBBBBBBBBBBBBBBB",
  "parent": 2,
  "labels": ["label 1", "label 3"],
  "email_domains": ["domain1.com", "domain3.com"],
  "fields": {
    "6": "some text"
  }
}
    """
    Then the response status code should be 204

    When I send a GET request to "/api/v2/organizations/3"
    Then the response should be in JSON
    And the response status code should be 200

    And the JSON node "data.id" should be equal to 3
    And the JSON node "data.name" should be equal to "Updated organization 3"
    And the JSON node "data.summary" should be equal to "updated test organization"
    And the JSON node "data.importance" should be equal to 5
    And the JSON node "data.parent" should be equal to 2
    And the JSON node "data.labels" should have 2 elements
    And the JSON node "data.labels[0]" should be equal to "label 1"
    And the JSON node "data.labels[1]" should be equal to "label 3"
    And the JSON node "data.email_domains" should have 2 elements
    And the JSON node "data.email_domains[0]" should be equal to "domain1.com"
    And the JSON node "data.email_domains[1]" should be equal to "domain3.com"
    And the JSON node "data.fields" should have 3 elements
    And the JSON node "data.fields.5.value" should exist
    And the JSON node "data.fields.6.value" should be equal to "some text"
    And the JSON node "data.fields.7.value" should be equal to 0

  Scenario: I try to create an organization with existing email domain
    When I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "Organization 3",
  "email_domains": ["domain1.com", "domain2.com"]
}
    """
    And print last JSON response
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "errors.fields.email_domains.fields.email_domains_0.errors[0].code" should be equal to "unique_entity"
    And the JSON node "errors.fields.email_domains.fields.email_domains_0.errors[0].message" should be equal to "This value already exists in the system."