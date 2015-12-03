Feature: User Profile
  To manage my account
  As a user
  I need a form so that I can make changes

  Background: Fresh DB
    Given I install the fresh data set

  @reinstall
  Scenario: I visit my profile page but I am not logged in
    When I go to "/profile"
    Then I should be on "/login"

  @basic
  Scenario: I visit my profile page
    Given I am authenticated as user
    When I go to "/profile"
    Then I should be on "/profile"