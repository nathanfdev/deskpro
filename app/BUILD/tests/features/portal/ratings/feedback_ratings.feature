Feature: Feedback ratings
  Users rating feedback

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  Scenario: I rate a feedback item positively as a user via I AGREE
    Given I login with user credentials
    And the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    When I follow "I Agree"
    And I press "Continue"
    Then I should be on "/feedback/view/example-feedback"
    And I should see a "success" flash message
    And I should see "Thank you for your feedback!"

  Scenario: I rate a feedback item positively as a GUEST via I AGREE
    Given the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    When I follow "I Agree"
    And I press "Continue"
    Then I should be on "/feedback/view/example-feedback"
    And I should see a "success" flash message
    And I should see "Thank you for your feedback!"