Feature: News commenting
  To share my thoughts
  As a user
  I want to post comments on news

  Background: Fresh database
    Given I install the fresh data set
    And I have "Example News Post" news
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And I disable anti-abuse rate limiting

  Scenario: I submit an invalid news comment as a guest
    And I am on "/news/posts/example-news-post"
    And I press "Save Comment"
    Then I should see a form error with "This value is required"

  Scenario: I comment on a news article as a guest
    Given I am on "/news/posts/example-news-post"
    When I fill in "Any comments?" with "This is a guest comment on a news post!"
    When I fill in "Your Name" with "Chris Name"
    When I fill in "Email" with "chris@deskpro.com"
    And I press "Save Comment"
    Then I should see a "success" flash message with the phrase "portal.flashes.guest_content_must_verify"
    And I should receive an email on "chris@deskpro.com" with the subject phrase "portal.email_subjects.validate-email"
    When I click the email verification link
    Then I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
    And I should be on the set password page

  Scenario: I use a registered email to comment as a guest
    Given I am on "/news/posts/example-news-post"
    When I fill in "Any comments?" with "This is a guest comment!"
    When I fill in "Your Name" with "Chris Name"
    When I fill in "Email" with "user@deskpro.dev"
    And I press "Save Comment"
    Then I should be on "/login"
    When I fill in "Your email" with "user@deskpro.dev"
    And I fill in "Your password" with "12345"
    And I press "Login"
    Then I should be on "/news/posts/example-news-post"
    And I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"

  Scenario: I submit an invalid news comment as a user
    Given I login with user credentials
    And I am on "/news/posts/example-news-post"
    And I press "Save Comment"
    Then I should see a form error with "This value is required"

  Scenario: I comment on a news post as a user
    Given I login with user credentials
    And I am on "/news/posts/example-news-post"
    When I fill in "Any comments?" with "This is my comment! I just posted it!!"
    And I press "Save Comment"
    Then I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
    And I should see "This is my comment! I just posted it!!"
