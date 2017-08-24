@new
Feature: Custom fields
  I want to add a custom field

  Background:
    Given I'm authenticated as admin

  Scenario: I create a ticket custom field with an alias
    When I send a POST request to "/api/v2/ticket_custom_fields" with body:
    """
{
  "title":"api-field-two",
  "alias":"zardoz",
  "is_enabled":true,
  "handler_class":"Application\\DeskPRO\\CustomFields\\Handler\\DataList"
}
    """

    Then the response status code should be 201

