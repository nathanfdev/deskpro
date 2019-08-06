Feature: Community topic commenting
  To share my thoughts
  As a user
  I want to post comments on community topic

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And I disable anti-abuse rate limiting

  Scenario: I comment on a community topic as a guest
    Given the "community" channel "Suggestion" exists with content titled "Example Topic"
    And I am on "/community/view/example-topic"
    When I fill in "Any comments?" with "This is a guest comment on a community topic!"
    When I fill in "Your Name" with "Chris Name"
    When I fill in "Email" with "chris@deskpro.com"
    And I press "Save Comment"
    Then I should see a "success" flash message with the phrase "portal.flashes.guest_content_must_verify"
    And I should receive an email on "chris@deskpro.com" with the subject phrase "portal.email_subjects.validate-email"
    When I click the email verification link received on "chris@deskpro.com"
    Then I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
    And I should be on the set password page

  Scenario: I use a registered email to comment as a guest
    Given the "community" channel "Suggestion" exists with content titled "Example Topic"
    And I am on "/community/view/example-topic"
    When I fill in "Any comments?" with "This is a guest comment!"
    When I fill in "Your Name" with "Chris Name"
    When I fill in "Email" with "user@deskpro.dev"
    And I press "Save Comment"
    Then I should be on "/login"
    When I fill in "login_username" with "user@deskpro.dev"
    And I fill in "login_password" with "12345"
    And I press "login_button"
    Then I should be on "/community/view/example-topic"
    And I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"

  Scenario: I submit an invalid community topic comment as a user
    Given I login with user credentials
    And the "community" channel "Suggestion" exists with content titled "Example Topic"
    And I am on "/community/view/example-topic"
    And I press "Save Comment"
    Then I should see a form error with "This value is required"

  Scenario: I submit an invalid community topic comment as a guest
    Given the "community" channel "Suggestion" exists with content titled "Example Topic"
    And I am on "/community/view/example-topic"
    And I press "Save Comment"
    Then I should see a form error with "This value is required"

  Scenario: I comment on a community topic as a user
    Given I login with user credentials
    And the "community" channel "Suggestion" exists with content titled "Example Topic"
    And I am on "/community/view/example-topic"
    When I fill in "Any comments?" with "This is my comment! I just posted it on a community topic!"
    And I press "Save Comment"
    Then I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
    And I should see "This is my comment! I just posted it on a community topic!"
