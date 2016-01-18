Feature: Feedback
  Clicking around the feedback, starting from the homepage

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And the "feedback" category "Suggestion" exists with content titled "Example Feedback"

  @reinstall
  Scenario: I visit the Feedback from the homepage
    Given I am on "/"
    When I follow "Feedback"
    Then I should be on "/feedback"
    And the response status code should be 200

  @reinstall
  Scenario: I visit a feedback item from the Feedback page
    Given I am on "/feedback"
    When I follow "Example Feedback"
    Then I should be on "/feedback/view/example-feedback"
    And the response status code should be 200
