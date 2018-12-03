Feature: Portal Modes
  In order to change the context of the user experience in the portal
  As a developer
  I need to be able to manipulate the mode via request url

  Background: fresh database
    Given I install the fresh data set

  Scenario: I don't do anything
    When I go to "/"
    Then the portal should be in normal mode

  Scenario: I specify an admin mode
    When I go to "/admin-mode"
    Then the portal should be in admin mode
