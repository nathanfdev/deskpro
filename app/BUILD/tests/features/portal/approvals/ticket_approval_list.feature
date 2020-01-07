Feature: Ticket approvals list
  Viewing a ticket approvals list

  Background: Fresh DB and fixtures
    Given I install the fresh data set
    And agent and user exist
    And I'm authenticated as user
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And no ApprovalResponse records exist
    And only the following Ticket records exist:
      | #  | person | subject   | status        |
      | t1 | {user} | My Ticket | awaiting_user |
    And the following ApprovalType records exist:
      | #   | name             | description              | isDeleted |
      | at1 | Approval Type 01 | This is approval type 01 | 0         |
    And only the following ApprovalTemplate records exist:
      | #    | type  | name        | description         | canApproversViewSubject | canChooseApprovers |
      | tmp1 | {at1} | Approval 01 | This is approval 01 | 1                       | 0                  |
    And only the following TicketApproval records exist:
      | #  | template | type  | ticket | description                               | name        | createdBy | approvers         |
      | a1 | {tmp1}   | {at1} | {t1}   | This is my actual approval description 01 | Approval 01 | {agent}   | [{user}, {agent}] |
      | a2 | {tmp1}   | {at1} | {t1}   | This is my actual approval description 02 | Approval 01 | {agent}   | [{user}, {agent}] |

  Scenario: Viewing the list of ticket approvals
    When I go to "/approvals"
    Then the ticket approvals table must contain 2 rows
    And row 1 of the ticket approvals table must contain the "Name" "Approval 01"
    And row 1 of the ticket approvals table must contain the "Description" "This is my actual approval description 01"
    And row 1 of the ticket approvals table must contain the "Your Response" "Awaiting Response"
    And row 2 of the ticket approvals table must contain the "Name" "Approval 01"
    And row 2 of the ticket approvals table must contain the "Description" "This is my actual approval description 02"
    And row 2 of the ticket approvals table must contain the "Your Response" "Awaiting Response"

  Scenario: Searching the list of ticket approvals
    When I go to "/approvals"
    Then fill in "search_ticket_approvals_q" with "approval description 01"
    And I press "search_ticket_approvals"
    Then the ticket approvals table must contain 1 rows
    And row 1 of the ticket approvals table must contain the "Name" "Approval 01"
    And row 1 of the ticket approvals table must contain the "Description" "This is my actual approval description 01"
    And row 1 of the ticket approvals table must contain the "Your Response" "Awaiting Response"
