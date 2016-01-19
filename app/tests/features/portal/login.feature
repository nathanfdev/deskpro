Feature: Login
  In order to use my account
  As a user
  I need a login form that works

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the sidebar theme

  @basic
  Scenario: I login as user
    When I login with user credentials
    Then I should be authenticated as user

  Scenario: I login as agent
    When I login with agent credentials
    Then I should be authenticated as agent

  Scenario: I login as admin
    When I login with admin credentials
    Then I should be authenticated as admin

# disabled for now, but should be re-enabled when the sidebar gets login capabilities
#  Scenario: I login via the sidebar
#    When I login using the sidebar with user credentials
#    Then I should be authenticated as user
