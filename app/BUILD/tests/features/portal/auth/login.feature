Feature: Login
  In order to use my account
  As a user
  I need a login form that works

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the sidebar theme

  Scenario: I login as user
    When I login with user credentials from the login page
    Then I should be authenticated as user

  Scenario: I login as agent
    When I login with agent credentials from the login page
    Then I should be authenticated as agent

  Scenario: I login as admin
    When I login with admin credentials from the login page
    Then I should be authenticated as admin

  Scenario: If I try to access a secured resource then I must be redirected to that resource after login
    Given the following tickets exist:
      | who  | subject        | ref            | status  |
      | user | My Test Ticket | YOMG-1633-MDDC | pending |
    When I am not logged in
    And I go to "/tickets/YOMG-1633-MDDC"
    Then I should be on "/login"
    When I fill in "username" with "user@deskpro.dev"
    And I fill in "password" with "12345"
    And I press "login_button"
    Then I should be on "/tickets/YOMG-1633-MDDC"
