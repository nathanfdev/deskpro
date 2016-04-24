@basic
Feature: User Profile
  To manage my account
  As a user
  I need a form so that I can make changes

  Background: Fresh DB
    Given I install the fresh data set

  Scenario: I visit my profile page but I am not logged in
    When I go to "/profile"
    Then I should be on "/login"

  Scenario: I visit my profile page
    Given I am authenticated as user
    When I go to "/profile"
    Then I should be on "/profile"

  Scenario: A user changes their name
    Given I login with user credentials
    And my name is "Ganon User"
    And I am on "/profile"
    When I fill in "person_profile_name" with "Luke Skywalker"
    And I press "Save Profile"
    Then my name should be "Luke Skywalker"
    Then I should see a "success" flash message with the phrase "portal.flashes.user_updated_profile"

  Scenario: A user changes their password and then logs in with the new one
    Given I login with user credentials
    And I am on "/profile"
    When I fill in "person_change_password_current_password" with "12345"
    When I fill in "person_change_password_new_password_password" with "MYNEWPASSWORD"
    When I fill in "person_change_password_new_password_confirm" with "MYNEWPASSWORD"
    And I press "Update Password"
    Then I should see a "success" flash message with the phrase "portal.flashes.user_changed_password"
    Given I follow "Logout"
    And I am on "/login"
    When I fill in "username" with "user@deskpro.dev"
    And I fill in "password" with "MYNEWPASSWORD"
    And I press "Login"
    Then I should be authenticated as user

  Scenario: A user tries to add a new email that is already taken by another account
    Given I login with user credentials
    And I am on "/profile/emails"
    When I fill in "Email" with "agent@deskpro.dev"
    And I press "Save"
    Then I should see a form error with "This value already exists in the system."

  Scenario: A user successfully adds and verifies an email
    Given I login with user credentials
    And I am on "/profile/emails"
    When I fill in "Email" with "new@email.com"
    And I press "Save"
    Then I should see a "success" flash message with the phrase "portal.flashes.user_add_email_verify"
    And I should receive an email with the subject phrase "portal.email_subjects.validate-email"
    When I click the email verification link
    Then I should see a "success" flash message with the phrase "portal.flashes.user_add_email_verified"
    And I should be on "/profile/emails"

  Scenario: A user changes their primary email address
    Given I login with user credentials
    And I have a verified email "verified@email.com"
    And I am on "/profile/emails"
    When I follow "verified@email.com"
    Then I should see a "success" flash message with the phrase "portal.flashes.user_changed_primary_email"
    And "verified@email.com" should be my primary email address
