Feature: submitting new community topic
  Users should be able to submit community topic

  Background: Fresh database
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And I disable anti-abuse rate limiting

  Scenario: A logged in user submits valid community topic
    Given I login with user credentials
    And I am on "/community"
    When I select "Suggestion" from "new_community_channel"
    And I fill in "new_community_topic_title" with "My New Community Topic Title"
    And I fill in "new_community_topic_content" with "I need to report the following bug. It happens when..."
    And I press "Add your community topic"
    Then I should be on "/community/view/my-new-community-topic-title"
    And the response status code should be 200
    And I should see a "success" flash message with the phrase "portal.flashes.new_community_topic_posted"
    And I should receive an email on user with the subject "Thank you for submitting your community topic"

  Scenario: A logged in user submits invalid community topic
    Given I login with user credentials
    And I am on "/community"
    When I select "Suggestion" from "new_community_channel"
    And I press "Add your community topic"
    Then I should be on "/community"
    Then I should see a form error with "This value is required"

  Scenario: A guest submits invalid community topic
    Given I am on "/community"
    When I select "Suggestion" from "new_community_channel"
    And I press "Add your community topic"
    Then I should be on "/community"
    Then I should see a form error with "This value is required"

  Scenario: A guest submits valid community topic and needs email verification
    Given I am on "/community"
    When I select "Suggestion" from "new_community_channel"
    And I fill in "new_community_topic_title" with "A Guest Community Topic Title"
    And I fill in "new_community_topic_content" with "I need to report the following bug. It happens when..."
    And I fill in "new_community_topic_name" with "Mr Guest"
    And I fill in "new_community_topic_email_email" with "guest@deskpro.com"
    And I press "Add your community topic"
    Then I should see a "success" flash message with the phrase "portal.flashes.guest_content_must_verify"
    And I should receive an email on "guest@deskpro.com" with the subject phrase "portal.email_subjects.validate-email"
    When I click the email verification link received on "guest@deskpro.com"
    Then I should see a "success" flash message with the phrase "portal.flashes.new_community_topic_posted"
    And I should be on the set password page