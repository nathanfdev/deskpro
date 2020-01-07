Feature: Ticket approval view
  Viewing a ticket approval

  Background: Fresh DB and fixtures
    Given I install the fresh data set
    And agent and user exist
    And I'm authenticated as user
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And only the following Ticket records exist:
      | #  | person | subject   | status        |
      | t1 | {user} | My Ticket | awaiting_user |
    And the following ApprovalType records exist:
      | #   | name             | description              | isDeleted |
      | at1 | Approval Type 01 | This is approval type 01 | 0         |
    And the following SelectedApprovers objects exist:
      | #   | hasTicketUser | hasOrganizationManagers | hasAllAgents | people   |
      | sa1 | 1             | 0                       | 0            | [{user}] |
    And only the following ApprovalTemplate records exist:
      | #    | type  | name        | description         | canApproversViewSubject | canChooseApprovers | selectedApprovers |
      | tmp1 | {at1} | Approval 01 | This is approval 01 | 1                       | 0                  | {sa1}             |
    And only the following TicketApproval records exist:
      | #  | template | type  | ticket | description                               | createdBy | name        | approvers         | status  | required approvals | required rejections |
      | a1 | {tmp1}   | {at1} | {t1}   | This is my actual approval description 01 | {user}    | Approval 01 | [{user}, {agent}] | pending | 2                  | 2                   |
    And no ApprovalResponse records exist

  Scenario: Viewing a ticket approval
    When I go to "/approvals/{a1}"
    Then I should see "Approval info"
    And I should see "Approval 01"

  Scenario: I partially approve a ticket approval
    When I go to "/approvals/{a1}"
    And I fill in "approval_response_message" with "I totally approve this"
    And I press "approval_response_approve"
    And I should see "I totally approve this"
    And I go to "/approvals/pending"
    Then the ticket approvals table must contain 1 rows
    And row 1 of the ticket approvals table must contain the "Your Response" "Approved"
    And I go to "/approvals/approved"
    Then the ticket approvals table must contain 0 rows
    And I go to "/approvals/rejected"
    Then the ticket approvals table must contain 0 rows

  Scenario: Ticket approval is fully approved
    When I go to "/approvals/{a1}"
    And I fill in "approval_response_message" with "I totally approve this"
    And I press "approval_response_approve"
    And I should see "I totally approve this"
    Given I login with agent credentials
    When I go to "/approvals/{a1}"
    And I fill in "approval_response_message" with "I also approve"
    And I press "approval_response_approve"
    And I should see "I also approve"
    And I go to "/approvals/pending"
    Then the ticket approvals table must contain 0 rows
    And I go to "/approvals/approved"
    Then the ticket approvals table must contain 1 rows
    And row 1 of the ticket approvals table must contain the "Your Response" "Approved"
    And I go to "/approvals/rejected"
    Then the ticket approvals table must contain 0 rows

  Scenario: Ticket approval is partially rejected
    When I go to "/approvals/{a1}"
    And I fill in "approval_response_message" with "I totally reject this"
    And I press "approval_response_reject"
    And I should see "I totally reject this"
    And I go to "/approvals/pending"
    Then the ticket approvals table must contain 1 rows
    And row 1 of the ticket approvals table must contain the "Your Response" "Rejected"
    And I go to "/approvals/approved"
    Then the ticket approvals table must contain 0 rows
    And I go to "/approvals/rejected"
    Then the ticket approvals table must contain 0 rows

  Scenario: Ticket approval is fully rejected
    When I go to "/approvals/{a1}"
    And I fill in "approval_response_message" with "I totally reject this"
    And I press "approval_response_reject"
    And I should see "I totally reject this"
    Given I login with agent credentials
    When I go to "/approvals/{a1}"
    And I fill in "approval_response_message" with "I also reject"
    And I press "approval_response_reject"
    And I should see "I also reject"
    And I go to "/approvals/pending"
    Then the ticket approvals table must contain 0 rows
    And I go to "/approvals/approved"
    Then the ticket approvals table must contain 0 rows
    And I go to "/approvals/rejected"
    Then the ticket approvals table must contain 1 rows
    And row 1 of the ticket approvals table must contain the "Your Response" "Rejected"
