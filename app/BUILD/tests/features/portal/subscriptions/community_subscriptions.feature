Feature: Community subscriptions
  Users can subscribe to community topic and community channels

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  Scenario: I subscribe to a community topic successfully as a user
    Given I login with user credentials
    And the "community" channel "Suggestion" exists with content titled "Example Topic"
    And I am on "/community/view/example-topic"
    When I follow "Subscribe"
    And I press "Continue"
    Then I should be on "/community/view/example-topic"
    And user should be subscribed to the community content "Example Topic"
    And I should see a "success" flash message

  Scenario: I cannot subscribe to a community topic as a GUEST
    Given the "community" channel "Suggestion" exists with content titled "Example Topic"
    And I am on "/community/view/example-topic"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200
