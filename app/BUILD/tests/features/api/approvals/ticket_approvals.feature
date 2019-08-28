@new
Feature: /ticket_approvals endpoint
  To CRUD DeskPRO ticket approvals
  As an API user
  I want an API endpoint

  Background:
    Given no ApprovalTemplate records exist
    And no TicketApproval records exist
    And no ApprovalResponse records exist
    And no ApprovalType records exist
    And agent and user exist
    And only the following Ticket records exist:
      | #  | Subject  | Person |
      | t1 | Ticket 1 | {user} |
      | t2 | Ticket 2 | {user} |

    And only the following ApprovalType records exist:
      | #      | name           | description    | isDeleted |
      | atype1 | Approval Type 1 | Description 1 | false     |
      | atype2 | Approval Type 2 | Description 2 | true      |

    And the following ApproverCriteria objects exist:
      | #      | agents    | allAgents | users | allUsers | organizationManagers | teams | departments | canChooseApprovers |
      | ac1    | [1]       | 0         | []    | 0        | 0                    | []    | []          | 1                  |
      | ac2    | [1,2,3]   | 0         | []    | 0        | 0                    | []    | []          | 0                  |
      | ac3    | []        | 1         | []    | 0        | 0                    | []    | []          | 1                  |

    And only the following ApprovalTemplate records exist:
      | #   | name    | description      | type     | requiredApprovals | requiredRejections | approverCriteria | canApproversViewSubject |
      | at1 | Templ 1 | Approval Templ 1 | {atype1} | 1                 | 1                  | {ac1}            | 1                       |
      | at2 | Templ 2 | Approval Templ 2 | {atype2} | 1                 | 1                  | {ac2}            | 0                       |
      | at3 | Templ 3 | Approval Templ 3 | {atype2} | 2                 | 0                  | {ac3}            | 1                       |

    And only the following TicketApproval records exist:
      | #   | ticket | template | approvers | name               | type     | description | status    |
      | ta1 | {t1}   | {at1}    | [1]       | Ticket approval 01 | {atype1} | TA 01       | pending   |
      | ta2 | {t1}   | {at2}    | [1,2,3]   | Ticket approval 02 | {atype2} | TA 02       | pending   |
      | ta3 | {t1}   | {at2}    | [1,2,3]   | Ticket approval 03 | {atype2} | TA 03       | completed |

  Scenario: I try to POST a ticket approval without authentication
    When I send a POST request to "/api/v2/tickets/{t1}/ticket_approvals"
    Then the response status code should be 401

  Scenario: I try to POST an ticket approval as a user
    Given I'm authenticated as "user"
    When I send a POST request to "/api/v2/tickets/{t1}/ticket_approvals"
    Then the response status code should be 403

  Scenario: I try to POST a ticket approval without required fields as agent
    Given I'm authenticated as "agent"
    When I send a POST request to "/api/v2/tickets/{t1}/ticket_approvals"
    And print last response
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.template.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.template.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I POST a ticket approval as admin and don't have enough approvers to meet min threshold
    Given I'm authenticated as "agent"
    When I send a POST request to "/api/v2/tickets/{t1}/ticket_approvals" with body:
            """
{
  "description": "Approval description 01",
  "template": ~at3~,
  "approvers": [1]
}
            """
    Then the response status code should be 400
    Then the response should be in JSON
    And the JSON node "errors.errors[0].message" should be equal to "There aren't enough approvers to meet the approval/rejection thresholds"

  Scenario: I POST a valid ticket approval as admin
    Given I'm authenticated as "agent"
    When I send a POST request to "/api/v2/tickets/{t1}/ticket_approvals" with body:
            """
{
  "description": "Approval description 01",
  "template": ~at1~,
  "approvers": [1]
}
            """
    Then the response status code should be 201
    Then the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    And the JSON node "data.name" should be equal to "Templ 1"
    And the JSON node "data.description" should be equal to "Approval description 01"
    And the JSON node "data.type" should be equal to "{atype1}"
    And the JSON node "data.required_approvals" should be equal to "1"
    And the JSON node "data.required_rejections" should be equal to "1"
    And the JSON node "data.can_approvers_view_subject" should be equal to true
    And the JSON node "data.status" should be equal to "pending"
    And the JSON node "data.approvers" should have "1" element
    And the JSON node "data.last_approved_response_at" should be null
    And the JSON node "data.last_reject_response_at" should be null
    And the JSON node "data.completed_at" should be null
    And the JSON node "data.cancelled_at" should be null
    And the JSON node "data.ticket" should be equal to "{t1}"
    And the JSON node "data.actions_on_create" should exist
    And the JSON node "data.actions_on_partial_approval_response" should exist
    And the JSON node "data.actions_on_partial_rejection_response" should exist
    And the JSON node "data.actions_on_cancel" should exist
    And the JSON node "data.actions_on_approved" should exist
    And the JSON node "data.actions_on_rejected" should exist

  Scenario: I GET a list of ticket approvals as an agent without authentication
    When I send a GET request to "/api/v2/tickets/{t1}/ticket_approvals"
    Then the response status code should be 401

  Scenario: I GET a list of ticket approvals as an agent
    Given I'm authenticated as "agent"
    When I send a GET request to "/api/v2/tickets/{t1}/ticket_approvals"
    Then the response status code should be 200
    And the JSON node "meta.pagination.total" should be equal to "3"
    And the JSON node "data[0].id" should be equal to "{ta1}"
    And the JSON node "data[0].name" should be equal to "Ticket approval 01"
    And the JSON node "data[0].description" should be equal to "TA 01"
    And the JSON node "data[1].id" should be equal to "{ta2}"
    And the JSON node "data[1].name" should be equal to "Ticket approval 02"
    And the JSON node "data[1].description" should be equal to "TA 02"
    And the JSON node "data[2].id" should be equal to "{ta3}"
    And the JSON node "data[2].name" should be equal to "Ticket approval 03"
    And the JSON node "data[2].description" should be equal to "TA 03"

  Scenario: I GET a count of ticket approvals as an agent without authentication
    When I send a GET request to "/api/v2/tickets/{t1}/ticket_approvals/counts"
    Then the response status code should be 401

  Scenario: I GET a count of ticket approvals as an agent
    Given I'm authenticated as "agent"
    When I send a GET request to "/api/v2/tickets/{t1}/ticket_approvals/counts"
    Then the response status code should be 200
    And the JSON node "data.count" should be equal to "3"

  Scenario: I PUT a cancellation status on a ticket approval without authentication
    When I send a PUT request to "/api/v2/ticket_approvals/{ta1}/cancel"
    Then the response status code should be 401

  Scenario: I PUT a cancellation status on a ticket approval
    Given I'm authenticated as "agent"
    When I send a PUT request to "/api/v2/ticket_approvals/{ta1}/cancel"
    Then the response status code should be 204

  Scenario: I PUT a cancellation status on a ticket approval that has already completed
    Given I'm authenticated as "agent"
    When I send a PUT request to "/api/v2/ticket_approvals/{ta3}/cancel"
    Then the response status code should be 400
    And the JSON node "code" should be equal to "Cannot cancel approval if it is already completed or cancelled"

  Scenario: I POST an approval response for a ticket approval without authentication
    When I send a POST request to "/api/v2/ticket_approvals/{ta2}/approve"
    Then the response status code should be 401

  Scenario: I POST an approval response for a ticket approval
    Given I'm authenticated as "agent"
    When I send a POST request to "/api/v2/ticket_approvals/{ta2}/approve" with body:
            """
{
  "message": "Testing message 01"
}
            """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    And the JSON node "data.vote_type" should be equal to "approve"
    And the JSON node "data.vote" should be equal to 1
    And the JSON node "data.message" should be equal to "Testing message 01"

  Scenario: I POST an approval response for a ticket approval twice
    Given I'm authenticated as "agent"
    When I send a POST request to "/api/v2/ticket_approvals/{ta2}/approve" with body:
            """
{
  "message": "Testing message 01"
}
            """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    When I send a POST request to "/api/v2/ticket_approvals/{ta2}/approve" with body:
            """
{
  "message": "Testing message 02"
}
            """
    Then the response status code should be 400
    And the JSON node "message" should be equal to "Approver, Zelda Agent, has responded to this approval before"

  Scenario: I POST an approval response for a ticket approval where I'm not a listed approver
    Given I'm authenticated as "agent"
    When I send a POST request to "/api/v2/ticket_approvals/{ta1}/approve" with body:
            """
{
  "message": "Testing message 01"
}
            """
    Then the response status code should be 400
    And the JSON node "message" should be equal to "Zelda Agent is not listed as an approver for this approval"

  Scenario: I POST an rejection response for a ticket approval without authentication
    When I send a POST request to "/api/v2/ticket_approvals/{ta2}/reject"
    Then the response status code should be 401

  Scenario: I POST an rejection response for a ticket approval
    Given I'm authenticated as "agent"
    When I send a POST request to "/api/v2/ticket_approvals/{ta2}/reject" with body:
            """
{
  "message": "Testing message 01"
}
            """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    And the JSON node "data.vote_type" should be equal to "reject"
    And the JSON node "data.vote" should be equal to "-1"
    And the JSON node "data.message" should be equal to "Testing message 01"

  Scenario: I POST an rejection response for a ticket approval twice
    Given I'm authenticated as "agent"
    When I send a POST request to "/api/v2/ticket_approvals/{ta2}/reject" with body:
            """
{
  "message": "Testing message 01"
}
            """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    When I send a POST request to "/api/v2/ticket_approvals/{ta2}/reject" with body:
            """
{
  "message": "Testing message 02"
}
            """
    Then the response status code should be 400
    And the JSON node "message" should be equal to "Approver, Zelda Agent, has responded to this approval before"

  Scenario: I POST an rejection response for a ticket approval where I'm not a listed approver
    Given I'm authenticated as "agent"
    When I send a POST request to "/api/v2/ticket_approvals/{ta1}/reject" with body:
            """
{
  "message": "Testing message 01"
}
            """
    Then the response status code should be 400
    And the JSON node "message" should be equal to "Zelda Agent is not listed as an approver for this approval"
