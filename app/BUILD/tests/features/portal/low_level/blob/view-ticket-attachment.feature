Feature: View Protected Ticket Attachment
  If Ticket Attachment require Auth
  As a user
  I need to see attachment if I have an access to ticket

  Background:
    Given there are no Blob records in the DB
    And the setting "core_tickets.attachment_require_auth" is set to "true"

  Scenario: I visit the view attachment page and I am unauthenticated
    When I go to "/ticket-attachment/abc"
    Then the url should match ".*/login"

  Scenario: I visit the view attachment page and I am authenticated as user and attachment doesn't exist
    Given I'm authenticated as user
    And I create blob with auth code "1196DYWABSMCWMAAAKN0T"
    When I go to "/ticket-attachment/1196DYWABSMCWMAAAKN0T"
    Then the response status code should be 404
    Then I should be on "/ticket-attachment/1196DYWABSMCWMAAAKN0T"

  Scenario: I visit the view attachment page and I am authenticated as user and have an access to ticket
    Given I'm authenticated as user
    And I create blob with auth code "1196DYWABSMCWMAAAKN0T"
    And only the following Ticket records exist:
      | #  | Subject | Person |
      | t1 | Ticket  | {user} |
    And only the following TicketMessage records exist:
      | #  | Ticket | Message |
      | m1 | {t1}   | Message |
    And only the following TicketAttachment records exist:
      | Ticket | Person | Message | Blob |
      | {t1}   | {user} | {m1}    | {blob_1196DYWABSMCWMAAAKN0T} |
    When I go to "/ticket-attachment/1196DYWABSMCWMAAAKN0T"
    Then I should be on "/file.php/1196DYWABSMCWMAAAKN0T/file.txt?access_token=.+$"

  Scenario: I visit the view attachment page and I am authenticated as user and don't have an access to ticket
    Given I'm authenticated as user
    And I create blob with auth code "1196DYWABSMCWMAAAKN0T"
    And a user with "user_1@deskpro.dev" email exists
    And only the following Ticket records exist:
      | #  | Subject | Person |
      | t1 | Ticket  | {user_1@deskpro.dev} |
    And only the following TicketMessage records exist:
      | #  | Ticket | Message |
      | m1 | {t1}   | Message |
    And only the following TicketAttachment records exist:
      | Ticket | Person | Message | Blob |
      | {t1}   | {user} | {m1}    | {blob_1196DYWABSMCWMAAAKN0T} |
    When I go to "/ticket-attachment/1196DYWABSMCWMAAAKN0T"
    Then the response status code should be 403
    Then I should be on "/ticket-attachment/1196DYWABSMCWMAAAKN0T"

  Scenario: I visit the view attachment page and I am authenticated as agent and have an access to ticket
    Given I'm authenticated as agent
    And I create blob with auth code "1196DYWABSMCWMAAAKN0T"
    And only the following Ticket records exist:
      | #  | Subject | Agent   |
      | t1 | Ticket  | {agent} |
    And only the following TicketMessage records exist:
      | #  | Ticket | Message |
      | m1 | {t1}   | Message |
    And only the following TicketAttachment records exist:
      | Ticket | Message | Blob |
      | {t1}   | {m1}    | {blob_1196DYWABSMCWMAAAKN0T} |
    When I go to "/ticket-attachment/1196DYWABSMCWMAAAKN0T"
    Then I should be on "/file.php/1196DYWABSMCWMAAAKN0T/file.txt?access_token=.+$"