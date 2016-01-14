Feature: Guests can submit new tickets
  Guests must be able to submit a new ticket if permissions allow it

  Background: Fresh DB
    Given I install the fresh data set
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  @reinstall
  Scenario: Submitting an invalid form
    Given I go to "/new-ticket"
    And I press "Submit"
    Then I should be on "/new-ticket"
    And I should see a form error with the phrase "portal.forms.error_ticket_department_required"

  @reinstall
  Scenario: Submitting an invalid form
    Given I go to "/new-ticket"
    And I select "Sales" from "Department"
    And I press "Submit"
    Then I should be on "/new-ticket"
    And I should see a form error with the phrase "portal.forms.error_ticket_subject_required"

  @reinstall
  Scenario: Submitting an invalid form
    Given I go to "/new-ticket"
    And I select "Sales" from "Department"
    And I fill in "Subject" with "This is a subject"
    And I press "Submit"
    Then I should be on "/new-ticket"
    And I should see a form error with the phrase "portal.forms.error_ticket_msg_required"

  @reinstall
  Scenario: Submitting an invalid form
    Given I go to "/new-ticket"
    And I select "Sales" from "Department"
    And I fill in "Subject" with "This is a subject"
    And I fill in "Message" with "Here is my ticket message"
    And I press "Submit"
    Then I should be on "/new-ticket"
    And I should see a form error with the phrase "portal.forms.error_email_required"

  @reinstall
  Scenario: Submitting a VALID FORM
    Given I go to "/new-ticket"
    And I select "Sales" from "Department"
    And I fill in "Subject" with "This is a subject"
    And I fill in "Message" with "Here is my ticket message"
    And I fill in "Email" with "my@email.com"
    And I press "Submit"
    Then I should be on "/thank-you"
    And I should see a success flash message with the phrase "portal.flashes.ticket_created"
