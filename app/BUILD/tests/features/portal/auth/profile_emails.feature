@new
Feature: User Profile
  Check emails management

  Background:
    Given there are no PersonEmail records
    And I have only default brand
    And I'm authenticated as user
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  Scenario: A user tries to add a new email that is already taken by another account
    Given an agent with "agent@deskpro.dev" email exists
    And I am on "/profile/emails"
    When I fill in "Email" with "agent@deskpro.dev"
    And I press "Save"
    Then I should see a form error with "is already in use by other user."
    Then I should see a form error with "agent@deskpro.dev"

  Scenario: A user successfully adds and verifies an email
    And I am on "/profile/emails"
    When I fill in "Email" with "new@email.com"
    And I press "Save"
    Then I should see a "success" flash message with the phrase "portal.flashes.user_add_email_verify"
    And I should receive an email on "new@email.com" with the subject phrase "portal.email_subjects.validate-email"
    When I click the email verification link received on "new@email.com"
    Then I should see a "success" flash message with the phrase "portal.flashes.user_add_email_verified"
    And I should be on "/profile/emails"

  Scenario: A user changes their primary email address
    And I have a verified email "verified@email.com"
    And I am on "/profile/emails"
    When I follow "verified@email.com"
    Then I should see a "success" flash message with the phrase "portal.flashes.user_changed_primary_email"
    And "verified@email.com" should be my primary email address

  Scenario: I try to save not valid email
    And I am on "/profile/emails"
    When I fill in "Email" with "bad_email.com"
    And I press "Save"
    Then I should see a form error with the phrase "bad_email.com"

  Scenario: I try to save empty email
    And I am on "/profile/emails"
    When I fill in "Email" with ""
    And I press "Save"
    Then I should see a form error with the phrase "portal.forms.error_required"
