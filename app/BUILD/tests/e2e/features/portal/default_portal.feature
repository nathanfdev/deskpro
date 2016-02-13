Feature: Portal
  A fresh installation should render the portal

  Background: Fresh database
    Given I install the fresh data set

  Scenario: I view the portal
    Given I am on "/"
    Then I should see "Welcome to DeskPRO"