@new
Feature: Custom currency field

  Background:
    Given I'm authenticated as user
    And only the following Currency records exist:
      | #  | Name          | Currency Code | Symbol |
      | c1 | US Dollar     | USD           | $      |
      | c2 | British Pound | GBP           | £      |
      | c2 | Euro          | EUR           | €      |
    And only the following custom ticket fields exist:
      | #  | Type     | Title          | Options               |
      | f1 | currency | Currency field | {"currency_id": ~c2~} |
    And the only default ticket layout exists with fields:
      | user_layout       |
      | ticket_field_{f1} |

  Scenario: I check the custom field is present on the form
    When I go to "/new-ticket"
    Then I should see the "ticket[ticket_field_{f1}][data]" field
