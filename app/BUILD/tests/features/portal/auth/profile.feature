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
    Given I'm authenticated as user
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
    And I press "login_button"
    Then I should be authenticated as user
