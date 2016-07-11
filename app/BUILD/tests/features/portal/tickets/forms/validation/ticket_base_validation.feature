@new
Feature: New ticket form validation
  I want to check base fields validation

  Scenario: I don't fill subject field
    Given I go to "/new-ticket"
    When I press "Submit"
    Then "ticket[subject]" form field should have error with the phrase "portal.forms.error_required"
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
    Then "ticket[message][message]" form field should have error with the phrase "portal.forms.error_required"
    And "ticket[message][message]" form field should have 1 error

  Scenario: I fill message with less than 10 chars length
    Given I go to "/new-ticket"
    When I fill in "Message" with "12356"
    And I press "Submit"
    Then "ticket[message][message]" form field should have error with the phrase "This value should have 10 characters or more"
    And "ticket[message][message]" form field should have 1 error
