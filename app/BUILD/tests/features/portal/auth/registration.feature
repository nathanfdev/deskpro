Feature: Registration
  Guests must be able to submit a new ticket if permissions allow it

  Background: Fresh DB
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  Scenario: Submitting an invalid form
    Given I go to "/register"
    And I press "Register"
    Then I should be on "/register"
    And I should see a form error with the phrase "portal.forms.error_required"

  Scenario: Submitting valid registration and verifying email
    Given I go to "/register"
    And I get captcha code from the form field "captcha"
    When I fill in "Name" with "Test User"
    And I fill in "person_registration_primary_email_email" with "testuser@deskpro.com"
    And I fill in "Password" with "password"
    And I fill in "Confirm" with "password"
    And I fill in "person_registration[captcha][captcha]" with "{captchaCode}"
    And I press "Register"
    Then I should be on "/"
    And I should see a success flash message with the phrase "portal.flashes.user_registered_must_verify"
    And I should receive an email on "testuser@deskpro.com" with the subject phrase "portal.email_subjects.validate-email"
    When I click the email verification link received on "testuser@deskpro.com"
    And I should be on "/"
    And I should see a success flash message with the phrase "portal.flashes.user_registered_verified_authenticated"

  Scenario: Trying to register with an email that exists will send you to the password
    Given I go to "/register"
    And I fill in "Name" with "Entering an Existing Email"
    And I fill in "person_registration_primary_email_email" with "user@deskpro.dev"
    And I fill in "Password" with "password"
    And I fill in "Confirm" with "password"
    And I press "Register"
    Then I should be on "/register/set-password"
    And I should receive an email with the subject phrase "user.email_subjects.password_set"
