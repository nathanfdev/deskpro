@new
Feature: New ticket form validation
  I want to check guest validation

  Scenario: I check person empty name
    When I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[person][user_name]" form field should have error with the phrase "portal.forms.error_required"
    And "ticket[person][user_name]" form field should have 1 error

  Scenario: I check person empty email
    When I go to "/new-ticket"
    And I press "Submit"
    Then "ticket[person][user_email][email]" form field should have error with the phrase "portal.forms.error_required"
    And "ticket[person][user_email][email]" form field should have 1 error

  Scenario: I check person bad email
    When I go to "/new-ticket"
    And I fill in "ticket[person][user_email][email]" with "12356"
    And I press "Submit"
    Then "ticket[person][user_email][email]" form field should have error with the phrase "This email address is not valid"
    And "ticket[person][user_email][email]" form field should have 1 error
