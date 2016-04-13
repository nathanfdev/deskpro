@people
Feature: Person emails collection CRUD

  Background:
    Given I install the api data set
    And there are no registered users
    And my request is authenticated

  Scenario: I try to create person w/o emails
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "John Doe"
}
    """
    Then the response status code should be 400

  Scenario: I create a person with a single email provided within the collection
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "John Doe",
  "emails": ["john@doe.org"]
}
    """
    Then the response status code should be 201
    And the JSON node "data.primary_email" should be equal to "john@doe.org"
    And the JSON node "data.emails[0]" should be equal to "john@doe.org"

  Scenario: I create a person with two emails
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "John Doe",
  "emails": ["john@doe.org", "john.doe@gmail.com"]
}
    """
    Then the response status code should be 201
    And the JSON node "data.primary_email" should be equal to "john@doe.org"
    And the JSON node "data.emails[0]" should be equal to "john@doe.org"
    And the JSON node "data.emails[1]" should be equal to "john.doe@gmail.com"

  Scenario: I delete a person then register using the same emails
    When I create a person with name "John Doe" and emails "john@doe.org, john.doe@gmail.com"
    And I delete just created person
    And I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "John Doe",
  "emails": ["john@doe.org", "john.doe@gmail.com"]
}
    """
    Then the response status code should be 201

  Scenario: I replace a single email
    Given I've just created a new person with name "Jane Doe" and primary email "jane@doe.org"
    When I send a PUT request to the just created person resource:
    """
{
  "emails": ["jane.doe@gmail.com"]
}
    """
    And I retrieve the person
    Then the response status code should be 200
    And the JSON node "data.emails" should have 1 element
    And the JSON node "data.primary_email" should be equal to "jane.doe@gmail.com"

  Scenario: I add a new email in addition to the existing one
    Given I create a person with name "Jane Doe" and emails "jane@doe.org"
    When I send a PUT request to the just created person resource:
    """
{
  "emails": ["jane@doe.org", "jane.doe@gmail.com"]
}
    """
    And I retrieve the person
    Then the response status code should be 200
    And the JSON node "data.emails" should have 2 elements
    And the JSON node "data.emails[1]" should be equal to "jane.doe@gmail.com"

  Scenario: I delete emails from collection
    Given I have a person with name "John Doe" and emails "john@doe.org, john.doe@gmail.com, work.john@doe.org"
    When I send a PUT request to the person resource:
    """
{
  "emails": ["john.doe@gmail.com"]
}
    """
    And I retrieve the person
    Then the JSON node "data.emails" should have 1 element
    And the JSON node "data.primary_email" should be equal to "john.doe@gmail.com"
    And the JSON node "data.emails[0]" should be equal to "john.doe@gmail.com"

  Scenario: I try to add an email used by another person
    Given I have a person with name "John Connor" and primary email "connor@cyberdyne.com"
    And "Sarah Connor" has just created an account with primary email "sarah@cyberdyne.com"
    When I send a PUT request to the just created person resource:
    """
{
  "emails": ["sarah@cyberdyne.com", "connor@cyberdyne.com"]
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.emails" should exist
  
  Scenario: I try to remove all emails
    Given I have a person with name "John Doe" and emails "john@doe.org"
    When I send a PUT request to the person resource:
    """
{
  "emails": []
}
    """
    Then the response status code should be 400

  Scenario: I remove all emails but provide the primary email
    Given I have a person with name "John Doe" and emails "john@doe.org"
    When I send a PUT request to the person resource:
    """
{
  "emails": [],
  "primary_email": "spam@doe.org"
}
    """
    And I retrieve the person
    Then the JSON node "data.emails" should have 1 element
    And the JSON node "data.emails[0]" should be equal to "spam@doe.org"
    And the JSON node "data.primary_email" should be equal to "spam@doe.org"

  Scenario: I create a user providing the same email as primary and within emails collection
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "John Doe",
  "primary_email": "spam@doe.org",
  "emails": ["john@doe.org", "spam@doe.org"]
}
    """
    Then the response status code should be 201
    And the JSON node "data.primary_email" should be equal to "spam@doe.org"
    And the JSON node "data.emails" should have 2 elements