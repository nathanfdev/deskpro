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

  Scenario: I check empty product
    Given  the setting "core.use_product" is set to 1
    And only the following Product records exist:
      | #  | Title     |
      | p1 | Product 1 |
      | p2 | Product 2 |
    And the only default ticket layout exists with fields:
      | user_layout |
      | product     |

    When I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[product]" form field should have error with the phrase "This value is required"
    And "ticket[product]" form field should have 1 error

  Scenario: I check empty priority
    Given  the setting "core.use_ticket_priority" is set to 1
    And only the following TicketPriority records exist:
      | #  | Title      |
      | p1 | Priority 1 |
      | p2 | Priority 2 |
    And the only default ticket layout exists with fields:
      | user_layout |
      | priority    |

    When I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[priority]" form field should have error with the phrase "This value is required"
    And "ticket[priority]" form field should have 1 error

  Scenario: I check empty category
    Given  the setting "core.use_ticket_category" is set to 1
    And only the following TicketCategory records exist:
      | #  | Title      |
      | c1 | Category 1 |
      | c2 | Category 2 |

    And the only default ticket layout exists with fields:
      | user_layout |
      | category    |

    When I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[category]" form field should have error with the phrase "This value is required"
    And "ticket[category]" form field should have 1 error

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
