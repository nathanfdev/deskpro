Feature: Feedback subscriptions
  Users can subscribe to feedback and feedback categories

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  @reinstall
  Scenario: I subscribe to a feedback item successfully as a user
    Given I login with user credentials
    And the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    When I follow "Subscribe"
    And I press "Continue"
    Then I should be on "/feedback/view/example-feedback"
    And user should be subscribed to the feedback content "Example Feedback"
    And I should see a "success" flash message

  Scenario: I cannot subscribe to a feedback item as a GUEST
    Given the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    When I follow "Subscribe"
    Then I should be on "/login"
    And the response status code should be 200
