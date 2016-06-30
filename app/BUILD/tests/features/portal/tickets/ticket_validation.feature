@new
Feature: New ticket form validation

  Scenario: I don't fill subject field
    Given I go to "/new-ticket"
    When I press "Submit"
    Then "ticket[subject]" form field should have error with the phrase "This value is required"
    And "ticket[subject]" form field should have 1 error

  Scenario: I fill subject with less than 5 chars length
    Given I go to "/new-ticket"
    When I fill in "Subject" with "123"
    And I press "Submit"
    Then "ticket[subject]" form field should have error with the phrase "This value should have 5 characters or more"
    And "ticket[subject]" form field should have 1 error

  Scenario: I don't fill message field
    Given I go to "/new-ticket"
    When I press "Submit"
    Then "ticket[message][message]" form field should have error with the phrase "This value is required"
    And "ticket[message][message]" form field should have 1 error

  Scenario: I fill message with less than 10 chars length
    Given I go to "/new-ticket"
    When I fill in "Message" with "12356"
    And I press "Submit"
    Then "ticket[message][message]" form field should have error with the phrase "This value should have 10 characters or more"
    And "ticket[message][message]" form field should have 1 error

  Scenario: I check empty department
    Given default everyone user group exits
    And only the following Department records exist:
      | #  | Title        | Is Tickets Enabled |
      | d1 | Department 1 | 1                  |
      | d2 | Department 2 | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup everyone
    And I grant the "{d2}" department permission of tickets app for usergroup everyone
    And I go to "/new-ticket"
    When I press "Submit"
    Then "ticket[department]" form field should have error with the phrase "This value is required"
    And "ticket[department]" form field should have 1 error

  Scenario Outline: I check required common field validation
    Given  the setting "core.<use_setting_name>" is set to 1
    And only the following <entity_type> records exist:
      | #  | Title   |
      | p1 | Title 1 |
      | p2 | Title 2 |
    And the only default ticket layout exists with fields:
      | user_layout  |
      | <field_name> |

    When the setting "core_tickets.field_validation_ticket_<validation_setting_name>_user_required" is set to 0
    And I go to "/new-ticket"
    And I press "Submit"
    And "ticket[<field_name>]" form field should have 0 errors

    When the setting "core_tickets.field_validation_ticket_<validation_setting_name>_user_required" is set to 1
    And I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[<field_name>]" form field should have error with the phrase "This value is required"
    And "ticket[<field_name>]" form field should have 1 error

    Examples:
      | entity_type    | field_name | use_setting_name    | validation_setting_name |
      | Product        | product    | use_product         | prod                    |
      | TicketPriority | priority   | use_ticket_priority | pri                     |
      | TicketCategory | category   | use_ticket_category | cat                     |

  Scenario: I check person empty name
    When I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[person][user_name]" form field should have error with the phrase "This value is required"
    And "ticket[person][user_name]" form field should have 1 error

  Scenario: I check person empty email
    When I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[person][user_email][email]" form field should have error with the phrase "This value is required"
    And "ticket[person][user_email][email]" form field should have 1 error

  Scenario: I check person bad email
    When I go to "/new-ticket"
    When I fill in "ticket[person][user_email][email]" with "12356"
    And I press "Submit"
    Then "ticket[person][user_email][email]" form field should have error with the phrase "This email address is not valid"
    And "ticket[person][user_email][email]" form field should have 1 error
