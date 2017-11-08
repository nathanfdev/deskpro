@new
Feature: New ticket form validation
  I want to check email validation

  Scenario: I check email domain
    When I go to "/new-ticket"
    And I fill in "ticket_person_user_email_email" with "user@gmail"
    And I press "Submit"
    Then "ticket_person_user_email_email" form field should have error with the phrase "This email address"
    And "ticket_person_user_email_email" form field should have 1 error
