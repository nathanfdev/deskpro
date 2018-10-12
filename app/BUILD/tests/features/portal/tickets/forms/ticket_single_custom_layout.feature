@new
Feature: Only 1 department w/ custom layout

  Background:
    Given I'm authenticated as user
    And I have only default brand
    And user has defaultBrand brand
    And I set permission "tickets.use" = 1 for "registered" usergroup
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered

  Scenario: I make sure the correct custom layout is applied when the department is set implicitly
    Given only the following custom ticket fields exist:
      | #  | Type     | Title          |
      | f1 | text     | Text field     |
      | f2 | textarea | Textarea field |
    And the ticket layout exists for "{d1}" department with fields:
      | user_layout       |
      | ticket_field_{f1} |
      | ticket_field_{f2} |

    When I go to "/new-ticket"
    Then the ".form-ticket" form should have 10 elements
    And I should see ".form-ticket" form fields in following order:
      | name                                 | class         |
      | ticket[ticket_field_{f1}][data]      |               |
      | ticket[ticket_field_{f2}][data]      |               |
      | ticket[department]                   |               |
      | ticket[subject]                      |               |
      | ticket[message][message]             |               |
      | ticket[message][format]              |               |
      | ticket[attachments][0][blob][upload] |               |
      | ticket[person][user_name]            |               |
      |                                      | email-present |
      | ticket[displayed_fields]             |               |
