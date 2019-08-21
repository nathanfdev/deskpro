@new
Feature: Ticket approval triggers
  To create and execute ticket approval trigger actions
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

    And only the following ApprovalType records exist:
      | #      | name           | description    | isDeleted |
      | atype1 | Approval Type 1 | Description 1 | false     |

  Scenario: I set up a valid ticket approval with a set ticket subject action
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_templates" with body:
            """
{
  "name": "Approval Template 1",
  "description": "Approval template 1 description",
  "type": ~atype1~,
  "required_approvals": 1,
  "required_rejections": 1,
  "can_approvers_view_subject": true,
  "approver_criteria": {
    "agents": [1]
  },
  "actions_on_approved": [{
			"type": "SetSubject",
			"options": {
				"subject": "Subject changed on approval",
				"with_formatter": false
			}
	}]
}
            """
    Then the response status code should be 201
    Then the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    When I send a POST request to "/api/v2/tickets/{t1}/ticket_approvals" with body:
            """
{
  "description": "Approval description 01",
  "template": ~lastCreatedId~,
  "approvers": [1]
}
            """
    Then the response status code should be 201
    Then the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    When I send a POST request to "/api/v2/ticket_approvals/{lastCreatedId}/approve" with body:
            """
{
  "message": "Testing message 01"
}
            """
    Then the response status code should be 201
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    And the JSON node "data.vote_type" should be equal to "approve"
    When I send a GET request to "/api/v2/tickets/{t1}"
    Then the response status code should be 200
    Then the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.subject" should be equal to "Subject changed on approval"
