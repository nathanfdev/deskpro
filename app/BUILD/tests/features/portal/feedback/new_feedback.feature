Feature: submitting new feedback
  Users should be able to submit feedback

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And I disable anti-abuse rate limiting

  Scenario: A logged in user submits valid feedback
    Given I login with user credentials
    And I am on "/feedback"
    When I select "Suggestion" from "new_feedback_category"
    And I fill in "new_feedback_title" with "My New Feedback Title"
    And I fill in "new_feedback_content" with "I need to report the following bug. It happens when..."
    And I press "Add your feedback"
    Then I should be on "/feedback/view/my-new-feedback-title"
    And the response status code should be 200
    And I should see a "success" flash message with the phrase "portal.flashes.new_feedback_posted"
    And I should receive an email on user with the subject "Thank you for submitting your feedback"

  Scenario: A logged in user submits invalid feedback
    Given I login with user credentials
    And I am on "/feedback"
    When I select "Suggestion" from "new_feedback_category"
    And I press "Add your feedback"
    Then I should be on "/feedback"
    Then I should see a form error with "This value is required"

  Scenario: A guest submits invalid feedback
    Given I am on "/feedback"
    When I select "Suggestion" from "new_feedback_category"
    And I press "Add your feedback"
    Then I should be on "/feedback"
    Then I should see a form error with "This value is required"

  Scenario: A guest submits valid feedback and needs email verification
    Given I am on "/feedback"
    When I select "Suggestion" from "new_feedback_category"
    And I fill in "new_feedback_title" with "A Guest Feedback Title"
    And I fill in "new_feedback_content" with "I need to report the following bug. It happens when..."
    And I fill in "new_feedback_name" with "Mr Guest"
    And I fill in "new_feedback_email_email" with "guest@deskpro.com"
    And I press "Add your feedback"
    Then I should see a "success" flash message with the phrase "portal.flashes.guest_content_must_verify"
    And I should receive an email on "guest@deskpro.com" with the subject phrase "portal.email_subjects.validate-email"
    When I click the email verification link received on "guest@deskpro.com"
    Then I should see a "success" flash message with the phrase "portal.flashes.new_feedback_posted"
    And I should be on the set password page

#  Scenario: A guest submits valid feedback and needs email verification
#    Given I am on "/feedback"
#    When I select "Suggestion" from "new_feedback_category"
#    And I fill in "new_feedback_title" with "A Guest Feedback Title"
#    And I fill in "new_feedback_content" with "I need to report the following bug. It happens when..."
#    And I fill in "new_feedback_custom_data_custom_feedback_def_1_data" with "Custom Text"
#    And I fill in "new_feedback_name" with "Un-authed User"
#    And I fill in "new_feedback_email_email" with "user@deskpro.dev"
#    And I press "Add your feedback"
#    Then I should be on "/login"
#    When I fill in "Email" with "user@deskpro.dev"
#    And I fill in "Your password" with "12345"
#    And I press "Login"
#    Then I should see a "success" flash message with the phrase "portal.flashes.new_feedback_posted"
#    And I should be on "/feedback/view/a-guest-feedback-title"
#    And the response status code should be 200
#    And I should see "A Guest Feedback Title"
#    And I should see "I need to report the following bug. It happens when..."
