@new
Feature: Mass action validation

  Background:
    Given I'm authenticated as agent
    And the setting "core.use_product" is set to 1
    And the setting "core.use_ticket_category" is set to 1
    And the setting "core_tickets.field_validation_ticket_cat_agent_required" is set to 1
    And I have only default brand
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And the only default ticket layout exists with fields:
      | agent_layout |
      | department   |
      | product      |
      | category     |
    And only the following Ticket records exist:
      | #  | Subject  | Department |
      | t1 | Ticket 1 | {d1}       |
      | t2 | Ticket 2 | {d1}       |
    And only the following Product records exist:
      | #  | Title     |
      | p1 | Product 1 |
      | p2 | Product 2 |
    And only the following TicketCategory records exist:
      | #  | Title      |
      | c1 | Category 1 |
      | c2 | Category 2 |

  Scenario: I check ticket ids permission validation
    Given I remove "agent" usergroup relation "agent_all_perms"
    And I remove "agent" usergroup relation "agent_all_safe_perms"
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~,~t2~],
  "params":{
     "set_product": ~p1~
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.ids.errors[0].code" should be equal to "no_permission"

  Scenario: I apply mass action even a ticket has validation errors
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~],
  "params":{
     "set_product": ~p1~
  }
}
    """
    Then the response status code should be 204

  Scenario: I try to unset required field via mass action
    When I send a POST request to "/api/v2/mass_actions/tickets" with body:
    """
{
  "ids": [~t1~],
  "params":{
     "set_category": null
  }
}
    """
    Then the response status code should be 400
    And the JSON node "errors.fields.ids" should not exist
    And the JSON node "errors.fields.params.fields.set_category.errors[0].code" should be equal to "required"
