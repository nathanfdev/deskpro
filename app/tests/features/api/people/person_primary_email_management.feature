Feature: Person primary email CRUD

  Background:
    Given I install the api data set
    And there are no registered users
    And my request is authenticated

  Scenario: I create a person with primary email
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "John Doe",
  "primary_email": "john@doe.org"
}
    """
    Then the response status code should be 201
    And the JSON node "data.primary_email" should be equal to "john@doe.org"
    And the JSON node "data.emails[0]" should be equal to "john@doe.org"

  Scenario: I add a primary email
    Given I've just created a new person with name "Jane Doe"
    When I send a PUT request to the just created person resource:
    """
{
  "primary_email": "jane.doe@gmail.com"
}
    """
    And I retrieve the person
    Then the response status code should be 200
    And the JSON node "data.primary_email" should be equal to "jane.doe@gmail.com"
    And the JSON node "data.emails" should have 1 element

  Scenario: I modify person's primary email
    Given I've just created a new person with name "Larry Doe" and primary email "larry@doe.name"
    When I send a PUT request to the just created person resource:
    """
{
  "primary_email": "larry.doe@gmail.com"
}
    """
    And I retrieve the person
    Then the response status code should be 200
    And the JSON node "data.primary_email" should be equal to "larry.doe@gmail.com"
    And the JSON node "data.emails" should have 2 elements
    And the JSON node "data.emails[0]" should be equal to "larry@doe.name"
    And the JSON node "data.emails[1]" should be equal to "larry.doe@gmail.com"

  Scenario: I modify person's primary email and email collection at the same time
    Given I've just created a new person with name "Larry Doe" and primary email "larry@doe.name"
    When I send a PUT request to the just created person resource:
    """
{
  "primary_email": "larry.doe@gmail.com",
  "emails": ["one@test.com", "two@test.com"]
}
    """
    And I retrieve the person
    Then the response status code should be 200
    And the JSON node "data.primary_email" should be equal to "larry.doe@gmail.com"
    And the JSON node "data.emails" should have 4 elements
    And the JSON node "data.emails[0]" should be equal to "larry@doe.name"
    And the JSON node "data.emails[1]" should be equal to "larry.doe@gmail.com"
    And the JSON node "data.emails[2]" should be equal to "one@test.com"
    And the JSON node "data.emails[3]" should be equal to "two@test.com"

  Scenario: I try to remove person's primary email
    Given "Julia Doe" has just created an account with primary email "julia@doe.name"
    When she sends a PUT request to modify her personal data:
    """
{
  "primary_email": null
}
    """
    Then the response status code should be 400

  Scenario: I try to create a person with primary email which is already taken
    Given I have a person with name "John Connor" and primary email "connor@gmail.com"
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "Sarah Connor",
  "primary_email": "connor@gmail.com"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.primary_email" should exist

  Scenario: I try to use existing email as person primary email
    Given I have a person with name "Bill Gates" and primary email "bill@microsoft.com"
    And "Anonymous Hacker" has just created an account with primary email "hacker@anonymous.org"
    When he sends a PUT request to modify his personal data:
    """
{
  "primary_email": "bill@microsoft.com"
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.primary_email" should exist
