Feature: Commenting
  To share my thoughts
  As a user
  I want to post comments on content

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme


  #
  # ARTICLES
  #

  @reinstall
  Scenario: I submit an invalid article comment as a user
    Given I login with user credentials
    And I am on "/kb/articles/example-article"
    And I press "Save Comment"
    Then I should see a form error with "This value is required"

  @reinstall
  Scenario: I submit an invalid article comment as a guest
    And I am on "/kb/articles/example-article"
    And I press "Save Comment"
    Then I should see a form error with "This value is required"

  @reinstall
  Scenario: I comment on an article as a user
    Given I login with user credentials
    And I am on "/kb/articles/example-article"
    When I fill in "What is your comment?" with "This is my comment! I just posted it!"
    And I press "Save Comment"
    Then I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
    And I should see "This is my comment! I just posted it!"

  @reinstall
  Scenario: I comment on an article as a guest and I click the email verification link
    Given I am on "/kb/articles/example-article"
    When I fill in "What is your comment?" with "This is a guest comment!"
    When I fill in "Your Name" with "Chris Name"
    When I fill in "Email" with "chris@deskpro.com"
    And I press "Save Comment"
    Then I should see a "success" flash message
    And I should see "Before we can post your content you must verify your email. Please check your email, we have sent you a verification link."
    And I should recieve an email with the subject phrase "portal.email_subjects.validate-email"
    When I click the email verification link
    Then I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
    And I should be on the set password page

  @reinstall
  Scenario: I use a registered email to comment as a guest
    Given I am on "/kb/articles/example-article"
    When I fill in "What is your comment?" with "This is a guest comment!"
    When I fill in "Your Name" with "Chris Name"
    When I fill in "Email" with "user@deskpro.dev"
    And I press "Save Comment"
    Then I should be on "/login"
    When I fill in "Email" with "user@deskpro.dev"
    And I fill in "Your password" with "12345"
    And I press "Login"
    Then I should be on "/kb/articles/example-article"
    And I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"


  #
  # NEWS
  #

  @reinstall
  Scenario: I submit an invalid news comment as a user
    Given I login with user credentials
    And I am on "/news/posts/example-news-post"
    And I press "Save Comment"
    Then I should see a form error with "This value is required"

  @reinstall
  Scenario: I submit an invalid news comment as a guest
    And I am on "/news/posts/example-news-post"
    And I press "Save Comment"
    Then I should see a form error with "This value is required"

  @reinstall
  Scenario: I comment on a news post as a user
    Given I login with user credentials
    And I am on "/news/posts/example-news-post"
    When I fill in "What is your comment?" with "This is my comment! I just posted it!!"
    And I press "Save Comment"
    Then I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
    And I should see "This is my comment! I just posted it!!"

  @reinstall
  Scenario: I comment on a news article as a guest
    Given I am on "/news/posts/example-news-post"
    When I fill in "What is your comment?" with "This is a guest comment on a news post!"
    When I fill in "Your Name" with "Chris Name"
    When I fill in "Email" with "chris@deskpro.com"
    And I press "Save Comment"
    Then I should see a "success" flash message
    And I should see "Before we can post your content you must verify your email. Please check your email, we have sent you a verification link."
    And I should recieve an email with the subject phrase "portal.email_subjects.validate-email"
    When I click the email verification link
    Then I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
    And I should be on the set password page

  @reinstall
  Scenario: I use a registered email to comment as a guest
    Given I am on "/news/posts/example-news-post"
    When I fill in "What is your comment?" with "This is a guest comment!"
    When I fill in "Your Name" with "Chris Name"
    When I fill in "Email" with "user@deskpro.dev"
    And I press "Save Comment"
    Then I should be on "/login"
    When I fill in "Email" with "user@deskpro.dev"
    And I fill in "Your password" with "12345"
    And I press "Login"
    Then I should be on "/news/posts/example-news-post"
    And I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"


  #
  # FEEDBACK
  #

  @reinstall
  Scenario: I submit an invalid feedback comment as a user
    Given I login with user credentials
    And the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    And I press "Save Comment"
    Then I should see a form error with "This value is required"

  @reinstall
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
    Then I should see a "success" flash message
    And I should see "Before we can post your content you must verify your email. Please check your email, we have sent you a verification link."
    And I should recieve an email with the subject phrase "portal.email_subjects.validate-email"
    When I click the email verification link
    Then I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
    And I should be on the set password page

  @reinstall
  Scenario: I use a registered email to comment as a guest
    Given the "feedback" category "Suggestion" exists with content titled "Example Feedback"
    And I am on "/feedback/view/example-feedback"
    When I fill in "What is your comment?" with "This is a guest comment!"
    When I fill in "Your Name" with "Chris Name"
    When I fill in "Email" with "user@deskpro.dev"
    And I press "Save Comment"
    Then I should be on "/login"
    When I fill in "Email" with "user@deskpro.dev"
    And I fill in "Your password" with "12345"
    And I press "Login"
    Then I should be on "/feedback/view/example-feedback"
    And I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"

  #
  # DOWNLOADS
  #

  @reinstall
  Scenario: I submit an invalid download comment as a user
    Given I login with user credentials
    And the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/files/example-download"
    And I press "Save Comment"
    Then I should see a form error with "This value is required"

  @reinstall
  Scenario: I submit an invalid download comment as a guest
    Given the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/files/example-download"
    And I press "Save Comment"
    Then I should see a form error with "This value is required"

  @reinstall
  Scenario: I comment on a download as a user
    Given I login with user credentials
    And the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/files/example-download"
    When I fill in "What is your comment?" with "This is my comment! I just posted it on a download!"
    And I press "Save Comment"
    Then I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
    And I should see "This is my comment! I just posted it on a download!"

  @reinstall
  Scenario: I comment on a download as a guest
    Given the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/files/example-download"
    When I fill in "What is your comment?" with "This is a guest comment on a download!"
    When I fill in "Your Name" with "Chris Name"
    When I fill in "Email" with "chris@deskpro.com"
    And I press "Save Comment"
    Then I should see a "success" flash message
    And I should see "Before we can post your content you must verify your email. Please check your email, we have sent you a verification link."
    And I should recieve an email with the subject phrase "portal.email_subjects.validate-email"
    When I click the email verification link
    Then I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
    And I should be on the set password page

  @reinstall
  Scenario: I use a registered email to comment as a guest
    Given the "download" category "General" exists with content titled "Example Download"
    And I am on "/downloads/files/example-download"
    When I fill in "What is your comment?" with "This is a guest comment!"
    When I fill in "Your Name" with "Chris Name"
    When I fill in "Email" with "user@deskpro.dev"
    And I press "Save Comment"
    Then I should be on "/login"
    When I fill in "Email" with "user@deskpro.dev"
    And I fill in "Your password" with "12345"
    And I press "Login"
    Then I should be on "/downloads/files/example-download"
    And I should see a "success" flash message with the phrase "portal.flashes.comment_thank_you"
