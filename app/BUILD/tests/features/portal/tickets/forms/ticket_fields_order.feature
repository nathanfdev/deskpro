@new
Feature: New ticket form
  I want to check fields order depends on layout

  Background:
    Given I'm authenticated as user
    And I have only default brand
    And user has defaultBrand brand
    And I disable anti-abuse rate limiting
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered
    And I set permission "tickets.use" = 1 for "registered" usergroup
    And only the following custom person fields exist:
      | #            | Type     | Title          |
      | user_field_1 | text     | Text field     |
      | user_field_2 | textarea | Textarea field |
    And only the following custom ticket fields exist:
      | #              | Type     | Title          |
      | ticket_field_1 | text     | Text field     |
      | ticket_field_2 | textarea | Textarea field |

  Scenario: I check user fields rendering before person
    Given the only default ticket layout exists with fields:
      | user_layout                   |
      | user_field_{user_field_1}     |
      | department                    |
      | person                        |
      | ticket_field_{ticket_field_1} |

    When I go to "/new-ticket"
    Then I should see ".form-ticket" form fields in following order:
      | name                                        | class         |
      | ticket[user_field_{user_field_1}][data]     |               |
      | ticket[department]                          |               |
      | ticket[person][user_name]                   |               |
      |                                             | email-present |
      | ticket[ticket_field_{ticket_field_1}][data] |               |

  Scenario: I change the layout
    Given the only default ticket layout exists with fields:
      | user_layout |
      | department  |
      | person      |
      | message     |
    And the ticket layout exists for "{d2}" department with fields:
      | user_layout |
      | person      |
      | message     |
      | department  |
    And I go to "/new-ticket"
    And I should see ".form-ticket" form fields in following order:
      | name                      | class         |
      | ticket[department]        |               |
      | ticket[person][user_name] |               |
      |                           | email-present |
      | ticket[message][message]  |               |

    When I select "Department 2" from "Department"
    And I press "Submit"
    Then I should see ".form-ticket" form fields in following order:
      | name                                 | class         |
      | ticket[person][user_name]            |               |
      |                                      | email-present |
      | ticket[message][message]             |               |
      | ticket[message][format]              |               |
      | ticket[attachments][0][blob][upload] |               |
      | ticket[department]                   |               |

  Scenario: I check required fields
    Given the only default ticket layout exists with fields:
      | user_layout               |
      | user_field_{user_field_1} |

    When I go to "/new-ticket"
    Then the ".form-ticket" form should have 9 elements
    And I should see ".form-ticket" form fields in following order:
      | name                                    | class         |
      | ticket[user_field_{user_field_1}][data] |               |
      | ticket[department]                      |               |
      | ticket[subject]                         |               |
      | ticket[message][message]                |               |
      | ticket[message][format]                 |               |
      | ticket[attachments][0][blob][upload]    |               |
      | ticket[person][user_name]               |               |
      |                                         | email-present |
      | ticket[displayed_fields]                |               |
