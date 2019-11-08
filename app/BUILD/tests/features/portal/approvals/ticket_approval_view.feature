Feature: Ticket approval view
  Viewing a ticket approval

  Background: Fresh DB and fixtures
    Given I install the fresh data set
    And approvals tables are cleared
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme
    And the following tickets exist:
      | ref | who   | subject                          | status         |
      | t1  | user  | My Ticket                        | awaiting_user  |
    And the following approval types exist:
      | ref | name             | description              | isDeleted |
      | at1 | Approval Type 01 | This is approval type 01 | 0         |
    And the following approval templates exist:
      | ref  | type | name        | description         | canApproversViewSubject |
      | tmp1 | at1  | Approval 01 | This is approval 01 | 1                       |
    And the following ticket approvals exist:
      | ref  | template | ticket | description                               |
      | a1   | tmp1     | t1     | This is my actual approval description 01 |

  Scenario: Viewing a ticket approval
    Given I login with user credentials
    When I view ticket approval a1
    And I should see "Approval info"
    And I should see "Approval 01"

  Scenario: I partially approve a ticket approval
    Given I login with user credentials
    When I view ticket approval a1
    And I fill in "approval_response_message" with "I totally approve this"
    And I press "approval_response_approve"
    And I should see "I totally approve this"
    And I visit "/approvals/pending"
    Then the ticket approvals table must contain 1 rows
    And row 1 of the ticket approvals table must contain the "Your Response" "Approved"
    And I visit "/approvals/approved"
    Then the ticket approvals table must contain 0 rows
    And I visit "/approvals/rejected"
    Then the ticket approvals table must contain 0 rows

  Scenario: Ticket approval is fully approved
    Given I login with user credentials
    When I view ticket approval a1
    And I fill in "approval_response_message" with "I totally approve this"
    And I press "approval_response_approve"
    And I should see "I totally approve this"
    Given I login with agent credentials
    When I view ticket approval a1
    And I fill in "approval_response_message" with "I also approve"
    And I press "approval_response_approve"
    And I should see "I also approve"
    And I visit "/approvals/pending"
    Then the ticket approvals table must contain 0 rows
    And I visit "/approvals/approved"
    Then the ticket approvals table must contain 1 rows
    And row 1 of the ticket approvals table must contain the "Your Response" "Approved"
    And I visit "/approvals/rejected"
    Then the ticket approvals table must contain 0 rows

  Scenario: Ticket approval is partially rejected
    Given I login with user credentials
    When I view ticket approval a1
    And I fill in "approval_response_message" with "I totally reject this"
    And I press "approval_response_reject"
    And I should see "I totally reject this"
    And I visit "/approvals/pending"
    Then the ticket approvals table must contain 1 rows
    And row 1 of the ticket approvals table must contain the "Your Response" "Rejected"
    And I visit "/approvals/approved"
    Then the ticket approvals table must contain 0 rows
    And I visit "/approvals/rejected"
    Then the ticket approvals table must contain 0 rows

  Scenario: Ticket approval is fully rejected
    Given I login with user credentials
    When I view ticket approval a1
    And I fill in "approval_response_message" with "I totally reject this"
    And I press "approval_response_reject"
    And I should see "I totally reject this"
    Given I login with agent credentials
    When I view ticket approval a1
    And I fill in "approval_response_message" with "I also reject"
    And I press "approval_response_reject"
    And I should see "I also reject"
    And I visit "/approvals/pending"
    Then the ticket approvals table must contain 0 rows
    And I visit "/approvals/approved"
    Then the ticket approvals table must contain 0 rows
    And I visit "/approvals/rejected"
    Then the ticket approvals table must contain 1 rows
    And row 1 of the ticket approvals table must contain the "Your Response" "Rejected"
