Feature: Guests can submit new tickets

  Background: Fresh DB
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  Scenario: Submitting a VALID FORM
    Given I go to "/new-ticket"
    And I select "Sales" from "Department"
    And I fill in "Subject" with "This is a subject"
    And I fill in "Message" with "Here is my ticket message"
    And I fill in "ticket_person_user_name" with "Chris Name"
    And I fill in "ticket_person_user_email_email" with "my@email.com"
    And I press "Submit"
    Then I should be on "/thank-you"
    And I should see a success flash message with the phrase "portal.flashes.ticket_created"

  Scenario: Submitting a VALID FORM but needing to validate email before submitting
    Given the setting "core_tickets.web_require_validation" is set to "1"
    And I go to "/new-ticket"
    And I select "Sales" from "Department"
    And I fill in "Subject" with "This is a subject"
    And I fill in "Message" with "Here is my ticket message"
    And I fill in "ticket_person_user_name" with "Chris Name"
    And I fill in "ticket_person_user_email_email" with "some@new.email"
    And I press "Submit"
    Then I should be on "/thank-you/verify-email"
    Then I should see a "success" flash message with the phrase "portal.flashes.guest_new_ticket_must_verify"
    And I should receive an email on "some@new.email" with the subject phrase "portal.email_subjects.email_new-confirm"
    When I click the email verification link received on "some@new.email"
    Then I should be on "/thank-you"
    And I should see a success flash message with the phrase "portal.flashes.ticket_created"

  Scenario: Submitting a VALID FORM but being forced to LOGIN
    Given I go to "/new-ticket"
    And I select "Sales" from "Department"
    And I fill in "Subject" with "This is a subject"
    And I fill in "Message" with "Here is my ticket message"
    And I fill in "ticket_person_user_name" with "Chris Name"
    And I fill in "ticket_person_user_email_email" with "user@deskpro.dev"

    And I press "Submit"
    Then I should be on "/login"
    When I fill in "login_username" with "user@deskpro.dev"
    And I fill in "login_password" with "12345"
    And I press "login_button"
    Then the url should match "/thank-you/[a-zA-Z0-9\-]+"
    And I should see a success flash message with the phrase "portal.flashes.ticket_created"
