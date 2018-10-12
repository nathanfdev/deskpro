@new
Feature: New ticket form
  I want to check default values from query params

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
    And only the following custom ticket fields exist:
      | #   | Type     | Title  |
      | tf1 | text     | FieldA |
      | tf2 | textarea | FieldB |
    And the only default ticket layout exists with fields:
      | user_layout        |
      | name               |
      | ticket_field_{tf1} |
      | ticket_field_{tf2} |

  Scenario: I check department override on the /new-ticket form
    When I go to "/new-ticket?department_id={d2}"
    Then the "Department" field should contain "{d2}"

  Scenario: I check department override on the widget's ticket form
    When I go to "/portal/api/tickets/new?department_id={d2}"
    Then the response should contain "<select dpx-select id=\\"ticket_department\\" name=\\"ticket[department]\\""

  Scenario: I check subject override on the /new-ticket form
    When I go to "/new-ticket?ticket[subject]=override%20value"
    Then the "Subject" field should contain "override value"

  Scenario: I check subject override on the widget's ticket form
    When I go to "/portal/api/tickets/new?ticket[subject]=override%20value"
    Then the response should contain "<input type=\\"text\\" id=\\"ticket_subject\\" name=\\"ticket[subject]\\" required=\\"required\\" value=\\"override value\\""

  Scenario: I check message override on the /new-ticket form
    When I go to "/new-ticket?ticket[message][message]=override%20value"
    Then the "Message" field should contain "override value"

  Scenario: I check message override on the widget's ticket form
    When I go to "/portal/api/tickets/new?ticket[message][message]=override%20value"
    Then the response should contain "<textarea id=\\"ticket_message_message\\" name=\\"ticket[message][message]\\" required=\\"required\\">override value"

  Scenario: I check custom fields on the /new-ticket form
    When I go to "/new-ticket?ticket[ticket_field_{tf1}][data]=val1&ticket[ticket_field_{tf2}][data]=val2"
    Then the "ticket_ticket_field_{tf1}_data" field should contain "val1"
    And the "ticket_ticket_field_{tf2}_data" field should contain "val2"

  Scenario: I check custom fields on the widget's ticket form
    When I go to "/portal/api/tickets/new?ticket[ticket_field_{tf1}][data]=val1&ticket[ticket_field_{tf2}][data]=val2"
    Then the response should contain "<input type=\\"text\\" id=\\"ticket_ticket_field_{tf1}_data\\" name=\\"ticket[ticket_field_{tf1}][data]\\" value=\\"val1\\""
    And the response should contain "<textarea id=\\"ticket_ticket_field_{tf2}_data\\" name=\\"ticket[ticket_field_{tf2}][data]\\">val2"

  Scenario: I check custom fields xss
    When I go to "/portal/api/tickets/new?ticket[ticket_field_{tf1}][data]=Default%20value<script>alert(%27foo%27);</script>&ticket[ticket_field_{tf2}][data]=Default%20value<script>alert(%27foo%27);</script>&ticket[message][message]=Default%20value<script>alert(%27foo%27);</script>"
    Then the response should contain "<input type=\\"text\\" id=\\"ticket_ticket_field_{tf1}_data\\" name=\\"ticket[ticket_field_{tf1}][data]\\" value=\\"Default value[script]alert"
    And the response should contain "<textarea id=\\"ticket_ticket_field_{tf2}_data\\" name=\\"ticket[ticket_field_{tf2}][data]\\">Default value[script]alert"
    And the response should contain "<textarea id=\\"ticket_message_message\\" name=\\"ticket[message][message]\\" required=\\"required\\">Default value<"

  Scenario: I check inline custom fields
    When I go to "/new-ticket?ticket[ticket_field_{tf1}]=val1&ticket[ticket_field_{tf2}]=val2"
    Then the "ticket_ticket_field_{tf1}_data" field should contain "val1"
    And the "ticket_ticket_field_{tf2}_data" field should contain "val2"
