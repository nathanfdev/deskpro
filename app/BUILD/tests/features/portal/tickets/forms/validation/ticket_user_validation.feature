@new
Feature: New ticket form validation
  I want to check logged in person validation

  Background:
    Given no Person records exist
    And I'm authenticated as user

  Scenario: I check logged in person name
    When I go to "/new-ticket"
    And I press "Submit"
    Then the "ticket[person][user_name]" field should contain "User User"
    And "ticket[person][user_name]" form field should have 0 error

  Scenario: I check selecting email
    Given only the following PersonEmail records exist:
      | #  | Person | Email              |
      | e1 | {user} | user_1@deskpro.dev |
      | e2 | {user} | user_2@deskpro.dev |
      | e3 | {user} | user_3@deskpro.dev |

    When I go to "/new-ticket"
    And I select "" from "Department"
    And I press "Submit"

    Then the "ticket[person][user_email]" field should contain "{e1}"
    And "ticket[person][user_email]" form field should have 0 error
