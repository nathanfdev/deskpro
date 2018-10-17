@new
Feature: Set entity id explicitly

  Background:
    Given no Person records exist
    And I'm authenticated as admin

  Scenario: I create a ticket with specific id
    Given no Ticket records exist
    When I send a POST request to "/api/v2/tickets" with body:
    """
{
  "subject": "My Ticket",
  "with_entity_id": 1000
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1000

  Scenario: I create a person with specific id
    When I send a POST request to "/api/v2/people" with body:
    """
{
  "name": "New Person",
  "primary_email": "my_email@example.com",
  "with_entity_id": 1000
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1000

  Scenario: I create an organization with specific id
    Given no Organization records exist
    When I send a POST request to "/api/v2/organizations" with body:
    """
{
  "name": "New Org",
  "with_entity_id": 1000
}
    """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to 1000
