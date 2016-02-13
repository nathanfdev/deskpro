Feature: View Tickets
  In order to see what is going on with my tickets
  As a user
  I need to see them in the portal

  Background: Fresh DB
    Given I install the fresh data set

  @reinstall
  Scenario: I visit the tickets page and I am unauthenticated
    When I go to "/tickets"
    Then I should be on "/login"

#  Scenario: I visit the tickets page
#    Given I am authenticated as user
#    When I go to the tickets page
#    Then I should see my tickets
#
#  Scenario: I view my ticket
#    Given I am authenticated as user
#    And I have a ticket
#    When I view my ticket
#    Then I should see my ticket