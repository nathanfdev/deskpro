Feature: Agent Bar
  To use additional features in the portal
  As an agent or admin user
  I want to see a toolbar when I login

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the sidebar theme

  @basic
  Scenario: A normal user logs in
    When I login with user credentials
    Then I should not see the agent bar

  @basic
  Scenario: An agent logs in and sees the agent bar without the admin dropdown
    When I login with agent credentials
    Then I should see the agent bar
    And the agent bar should not have the admin dropdown

  @basic
  Scenario: An admin logs in and sees the agent bar with the admin dropdown
    When I login with admin credentials
    Then I should see the agent bar
    And the agent bar should have the admin dropdown
