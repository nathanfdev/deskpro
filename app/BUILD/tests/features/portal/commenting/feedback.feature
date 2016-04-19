Feature: Feedback commenting
  To share my thoughts
  As a user
  I want to post comments on feedback

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  Scenario: I submit an invalid feedback comment as a user
    Given I login with user credentials
    And the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    And I press "Save Comment"
    Then I should see a form error with "This value is required"

  Scenario: I submit an invalid feedback comment as a guest
    Given the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    And I press "Save Comment"
    Then I should see a form error with "This value is required"

  @reinstall
  Scenario: I comment on a feedback item as a user
    Given I login with user credentials
    And the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    When I fill in "What is your comment?" with "This is my comment! I just posted it on a feedback item!"
    And I press "Save Comment"
    Then I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
    And I should see "This is my comment! I just posted it on a feedback item!"

  @reinstall
  Scenario: I comment on a feedback item as a guest
    Given the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    When I fill in "What is your comment?" with "This is a guest comment on a feedback item!"
    When I fill in "Your Name" with "Chris Name"
    When I fill in "Email" with "chris@deskpro.com"
    And I press "Save Comment"
    Then I should see a "success" flash message with the phrase "portal.flashes.guest_content_must_verify"
    And I should receive an email with the subject phrase "portal.email_subjects.validate-email"
    When I click the email verification link
    Then I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
    And I should be on the set password page

  Scenario: I use a registered email to comment as a guest
    Given the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    When I fill in "What is your comment?" with "This is a guest comment!"
    When I fill in "Your Name" with "Chris Name"
    When I fill in "Email" with "user@deskpro.dev"
    And I press "Save Comment"
    Then I should be on "/login"
    When I fill in "Your email" with "user@deskpro.dev"
    And I fill in "Your password" with "12345"
    And I press "Login"
    Then I should be on "/feedback/view/example-feedback"
    And I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
