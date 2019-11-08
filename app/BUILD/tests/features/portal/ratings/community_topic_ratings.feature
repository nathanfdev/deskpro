Feature: Community topic ratings
  Users rating community topic

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  Scenario: I rate a community topic positively as a user via I AGREE
    Given I login with user credentials
    And the "community" forum "Suggestion" exists with content titled "Example Topic"
    And I am on "/community/view/example-topic"
    When I follow "I Agree"
    And I press "Continue"
    Then I should be on "/community/view/example-topic"
    And I should see a "success" flash message
    And I should see "Thank you for your feedback!"

  Scenario: I rate a community topic positively as a GUEST via I AGREE
    Given the "community" forum "Suggestion" exists with content titled "Example Topic"
    And I am on "/community/view/example-topic"
    When I follow "I Agree"
    And I press "Continue"
    Then I should be on "/community/view/example-topic"
    And I should see a "success" flash message
    And I should see "Thank you for your feedback!"
