@new
Feature: /approval_templates endpoint
  To CRUD DeskPRO approval templates
  As an API user
  I want an API endpoint

  Background:
    Given no ApprovalTemplate records exist
    And no TicketApproval records exist
    And no ApprovalResponse records exist
    And no ApprovalType records exist
    And only the following ApprovalType records exist:
      | #      | name           | description    | isDeleted |
      | atype1 | Approval Type 1 | Description 1 | false     |
      | atype2 | Approval Type 2 | Description 2 | true      |

    And the following ApproverCriteria objects exist:
      | #      | agents    | allAgents | users | allUsers | organizationManagers | teams | departments |
      | ac1    | [1]       | 0         | []    | 0        | 0                    | []    | []          |
      | ac2    | [1,2]     | 0         | []    | 0        | 0                    | []    | []          |

    And only the following ApprovalTemplate records exist:
      | #   | name             | description      | type     | requiredApprovals | requiredRejections | approverCriteria | canApproversViewSubject |
      | at1 | Templ 1          | Approval Templ 1 | {atype1} | 1                 | 4                  | {ac1}            | 1                       |
      | at2 | Templ 2          | Approval Templ 2 | {atype2} | 2                 | 1                  | {ac2}            | 0                       |

  Scenario: I try to POST an approval template without authentication
    When I send a POST request to "/api/v2/approval_templates"
    Then the response status code should be 401

  Scenario: I try to POST an approval template as an agent
    Given I'm authenticated as "agent"
    When I send a POST request to "/api/v2/approval_templates"
    Then the response status code should be 403

  Scenario: I try to POST an approval template as a user
    Given I'm authenticated as "user"
    When I send a POST request to "/api/v2/approval_templates"
    Then the response status code should be 403

  Scenario: I try to POST an approval template without required fields as admin
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_templates"
    Then the response should be in JSON
    And the response status code should be 400
    And the JSON node "errors.fields.type.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.type.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.name.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.name.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.required_approvals.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.required_approvals.errors[0].message" should be equal to "This value should not be blank."
    And the JSON node "errors.fields.required_rejections.errors[0].code" should be equal to "required"
    And the JSON node "errors.fields.required_rejections.errors[0].message" should be equal to "This value should not be blank."

  Scenario: I POST an approval template as admin with invalid agents in criteria
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_templates" with body:
            """
{
  "name": "Approval Template 1",
  "description": "Approval template 1 description",
  "type": ~atype1~,
  "required_approvals": 2,
  "required_rejections": 3,
  "can_approvers_view_subject": true,
  "approver_criteria": {
    "can_choose_approvers": true,
    "agents": [99999]
  }
}
            """
    Then the response status code should be 400
    Then the response should be in JSON
    And the JSON node "errors.errors[0].message" should be equal to "Invalid list of agents"

  Scenario: I POST an approval template as admin with invalid users in criteria
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_templates" with body:
            """
{
  "name": "Approval Template 1",
  "description": "Approval template 1 description",
  "type": ~atype1~,
  "required_approvals": 2,
  "required_rejections": 3,
  "can_approvers_view_subject": true,
  "approver_criteria": {
    "can_choose_approvers": true,
    "users": [99999]
  }
}
            """
    Then the response status code should be 400
    Then the response should be in JSON
    And the JSON node "errors.errors[0].message" should be equal to "Invalid list of users"

  Scenario: I POST an approval template as admin with invalid approval and rejection thresholds
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_templates" with body:
            """
{
  "name": "Approval Template 1",
  "description": "Approval template 1 description",
  "type": ~atype1~,
  "required_approvals": 0,
  "required_rejections": 0,
  "can_approvers_view_subject": true,
  "approver_criteria": {
    "agents": [1]
  }
}
            """
    Then the response status code should be 400
    Then the response should be in JSON
    And the JSON node "errors.fields.required_approvals.errors" should have "1" element
    And the JSON node "errors.fields.required_rejections.errors" should have "1" element

  Scenario: I POST an approval template as admin with threshold that does not meet minimum number of approvers
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_templates" with body:
            """
{
  "name": "Approval Template 1",
  "description": "Approval template 1 description",
  "type": ~atype1~,
  "required_approvals": 2,
  "required_rejections": 0,
  "can_approvers_view_subject": true,
  "approver_criteria": {
    "can_choose_approvers": false,
    "agents": [1]
  }
}
            """
    Then the response status code should be 400
    Then the response should be in JSON
    And the JSON node "errors.errors[0].message" should be equal to "There aren't enough approvers to meet the approval/rejection thresholds"

  Scenario: I POST an approval template as admin with threshold that does not meet minimum number of approvers but is valid as agent can approve approvers later
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_templates" with body:
            """
{
  "name": "Approval Template 1",
  "description": "Approval template 1 description",
  "type": ~atype1~,
  "required_approvals": 2,
  "required_rejections": 0,
  "can_approvers_view_subject": true,
  "approver_criteria": {
    "can_choose_approvers": true,
    "agents": [1]
  }
}
            """
    Then the response status code should be 201
    Then the response should be in JSON

  Scenario: I POST an approval template as admin with invalid users in criteria
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_templates" with body:
            """
{
  "name": "Approval Template 1",
  "description": "Approval template 1 description",
  "type": ~atype1~,
  "required_approvals": 2,
  "required_rejections": 3,
  "can_approvers_view_subject": true,
  "approver_criteria": {
    "can_choose_approvers": true,
    "users": [99999]
  }
}
            """
    Then the response status code should be 400
    Then the response should be in JSON
    And the JSON node "errors.errors[0].message" should be equal to "Invalid list of users"

  Scenario: I POST an approval template as admin with invalid teams in criteria
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_templates" with body:
            """
{
  "name": "Approval Template 1",
  "description": "Approval template 1 description",
  "type": ~atype1~,
  "required_approvals": 2,
  "required_rejections": 3,
  "can_approvers_view_subject": true,
  "approver_criteria": {
    "can_choose_approvers": true,
    "teams": [99999]
  }
}
            """
    Then the response status code should be 400
    Then the response should be in JSON
    And the JSON node "errors.errors[0].message" should be equal to "Invalid list of teams"

  Scenario: I POST an approval template as admin with invalid departments in criteria
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_templates" with body:
            """
{
  "name": "Approval Template 1",
  "description": "Approval template 1 description",
  "type": ~atype1~,
  "required_approvals": 2,
  "required_rejections": 3,
  "can_approvers_view_subject": true,
  "approver_criteria": {
    "can_choose_approvers": true,
    "departments": [99999]
  }
}
            """
    Then the response status code should be 400
    Then the response should be in JSON
    And the JSON node "errors.errors[0].message" should be equal to "Invalid list of departments"

  Scenario: I POST a valid approval template as admin
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_templates" with body:
            """
{
  "name": "Approval Template 1",
  "description": "Approval template 1 description",
  "type": ~atype1~,
  "required_approvals": 2,
  "required_rejections": 3,
  "can_approvers_view_subject": true,
  "approver_criteria": {
    "agents": [1, 2]
  }
}
            """
    Then the response status code should be 201
    Then the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    And the JSON node "data.name" should be equal to "Approval Template 1"
    And the JSON node "data.description" should be equal to "Approval template 1 description"
    And the JSON node "data.type" should be equal to "{atype1}"
    And the JSON node "data.required_approvals" should be equal to "2"
    And the JSON node "data.required_rejections" should be equal to "3"
    And the JSON node "data.can_approvers_view_subject" should be true
    And the JSON node "data.approver_criteria.agents" should have "2" elements
    And the JSON node "data.actions_on_create" should exist
    And the JSON node "data.actions_on_partial_approval_response" should exist
    And the JSON node "data.actions_on_partial_rejection_response" should exist
    And the JSON node "data.actions_on_cancel" should exist
    And the JSON node "data.actions_on_approved" should exist
    And the JSON node "data.actions_on_rejected" should exist

  Scenario: I POST a valid approval template as admin with trigger actions
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_templates" with body:
            """
{
  "name": "Approval Template 1",
  "description": "Approval template 1 description",
  "type": ~atype1~,
  "required_approvals": 2,
  "required_rejections": 3,
  "can_approvers_view_subject": true,
  "approver_criteria": {
    "agents": [1, 2]
  },
  "actions_on_create": [{
    "type": "SendUserEmail",
    "options": {
      "template": "DeskPRO:emails_user:ticket-new-autoreply.html.twig",
			"do_cc_users": false,
			"from_name": "helpdesk_name",
			"from_account": 0,
			"headers": {
			  "X-FunctionalTestToken": "A"
			}
    }
  }],
  "actions_on_partial_approval_response": [{
    "type": "SendUserEmail",
    "options": {
      "template": "DeskPRO:emails_user:ticket-new-autoreply.html.twig",
			"do_cc_users": false,
			"from_name": "helpdesk_name",
			"from_account": 0,
			"headers": {
			  "X-FunctionalTestToken": "B"
			}
    }
  }],
  "actions_on_partial_rejection_response": [{
    "type": "SendUserEmail",
    "options": {
      "template": "DeskPRO:emails_user:ticket-new-autoreply.html.twig",
			"do_cc_users": false,
			"from_name": "helpdesk_name",
			"from_account": 0,
			"headers": {
			  "X-FunctionalTestToken": "C"
			}
    }
  }],
  "actions_on_cancel": [{
    "type": "SendUserEmail",
    "options": {
      "template": "DeskPRO:emails_user:ticket-new-autoreply.html.twig",
			"do_cc_users": false,
			"from_name": "helpdesk_name",
			"from_account": 0,
			"headers": {
			  "X-FunctionalTestToken": "D"
			}
    }
  }],
  "actions_on_approved": [{
    "type": "SendUserEmail",
    "options": {
      "template": "DeskPRO:emails_user:ticket-new-autoreply.html.twig",
			"do_cc_users": false,
			"from_name": "helpdesk_name",
			"from_account": 0,
			"headers": {
			  "X-FunctionalTestToken": "E"
			}
    }
  }],
  "actions_on_rejected": [{
    "type": "SendUserEmail",
    "options": {
      "template": "DeskPRO:emails_user:ticket-new-autoreply.html.twig",
			"do_cc_users": false,
			"from_name": "helpdesk_name",
			"from_account": 0,
			"headers": {
			  "X-FunctionalTestToken": "F"
			}
    }
  }]
}
            """
    Then the response status code should be 201
    Then the response should be in JSON
    And the JSON node "data" should exist
    And the JSON node "data.id" should be equal to "{lastCreatedId}"
    And the JSON node "data.name" should be equal to "Approval Template 1"
    And the JSON node "data.description" should be equal to "Approval template 1 description"
    And the JSON node "data.type" should be equal to "{atype1}"
    And the JSON node "data.required_approvals" should be equal to "2"
    And the JSON node "data.required_rejections" should be equal to "3"
    And the JSON node "data.can_approvers_view_subject" should be true
    And the JSON node "data.approver_criteria.agents" should have "2" elements
    And the JSON node "data.actions_on_create" should exist
    And the JSON node "data.actions_on_partial_approval_response" should exist
    And the JSON node "data.actions_on_partial_rejection_response" should exist
    And the JSON node "data.actions_on_cancel" should exist
    And the JSON node "data.actions_on_approved" should exist
    And the JSON node "data.actions_on_rejected" should exist
    And the JSON node "data.actions_on_create.actions[0].type" should be equal to "SendUserEmail"
    And the JSON node "data.actions_on_create.actions[0].options.template" should be equal to "DeskPRO:emails_user:ticket-new-autoreply.html.twig"
    And the JSON node "data.actions_on_create.actions[0].options.do_cc_users" should be null
    And the JSON node "data.actions_on_create.actions[0].options.from_name" should be equal to "helpdesk_name"
    And the JSON node "data.actions_on_create.actions[0].options.from_account" should be equal to "0"
    And the JSON node "data.actions_on_create.actions[0].options.headers.X-FunctionalTestToken" should be equal to "A"
    And the JSON node "data.actions_on_partial_approval_response.actions[0].options.headers.X-FunctionalTestToken" should be equal to "B"
    And the JSON node "data.actions_on_partial_rejection_response.actions[0].options.headers.X-FunctionalTestToken" should be equal to "C"
    And the JSON node "data.actions_on_cancel.actions[0].options.headers.X-FunctionalTestToken" should be equal to "D"
    And the JSON node "data.actions_on_approved.actions[0].options.headers.X-FunctionalTestToken" should be equal to "E"
    And the JSON node "data.actions_on_rejected.actions[0].options.headers.X-FunctionalTestToken" should be equal to "F"

  Scenario: I POST a valid approval template as admin
    Given I'm authenticated as "admin"
    When I send a POST request to "/api/v2/approval_templates" with body:
            """
{
  "name": "Approval Template 1",
  "description": "Approval template 1 description",
  "type": ~atype1~,
  "required_approvals": 2,
  "required_rejections": 3,
  "can_approvers_view_subject": true,
  "approver_criteria": { }
}
            """
    Then the response status code should be 400
    Then the response should be in JSON
    And the JSON node "errors.errors[0].message" should be equal to "There aren't enough approvers to meet the approval/rejection thresholds"
    And the JSON node "errors.errors[1].message" should be equal to "At least one approver criteria must be provided"
    And the JSON node "errors.errors[2].message" should be equal to "If an agent cannot choose approvers, then agent and/or user approvers must be supplied"


  Scenario: I try to GET an approval template without authentication
    When I send a GET request to "/api/v2/approval_templates/{at1}"
    Then the response status code should be 401

  Scenario: I GET an approval template which exists as user
    Given I'm authenticated as "user"
    When I send a GET request to "/api/v2/approval_templates/{at1}"
    Then the response status code should be 403

  Scenario: I GET an approval template which exists as agent
    Given I'm authenticated as "agent"
    When I send a GET request to "/api/v2/approval_templates/{at1}"
    Then the response status code should be 200

  Scenario: I GET an approval template which does not exist as admin
    Given I'm authenticated as "admin"
    When I send a GET request to "/api/v2/approval_templates/99999"
    Then the response status code should be 404
    Then the response should be in JSON
    And the JSON node "code" should be equal to "#99999 Not Found"
    And the JSON node "message" should be equal to "#99999 Not Found"

  Scenario: I GET a valid approval template as admin
    Given I'm authenticated as "admin"
    When I send a GET request to "/api/v2/approval_templates/{at1}"
    Then the response status code should be 200
    Then the response should be in JSON
    And the JSON node "data.id" should be equal to "{at1}"
    And the JSON node "data.name" should be equal to "Templ 1"
    And the JSON node "data.description" should be equal to "Approval Templ 1"
    And the JSON node "data.type" should be equal to "{atype1}"
    And the JSON node "data.required_approvals" should be equal to "1"
    And the JSON node "data.required_rejections" should be equal to "4"
    And the JSON node "data.can_approvers_view_subject" should be true
    And the JSON node "data.approver_criteria.agents[0]" should be equal to "1"

  Scenario: I try to GET a list of approval templates without authentication
    When I send a GET request to "/api/v2/approval_templates"
    Then the response status code should be 401

  Scenario: I GET a list of approval templates as user
    Given I'm authenticated as "user"
    When I send a GET request to "/api/v2/approval_templates"
    Then the response status code should be 403

  Scenario: I GET a list of approval templates as agent
    Given I'm authenticated as "agent"
    When I send a GET request to "/api/v2/approval_templates"
    Then the response status code should be 200
    And the JSON node "meta.pagination.total" should be equal to "2"

  Scenario: I GET a list of approval templates as admin
    Given I'm authenticated as "admin"
    When I send a GET request to "/api/v2/approval_templates"
    Then the response status code should be 200
    And the JSON node "meta.pagination.total" should be equal to "2"
    And the JSON node "data[0].id" should be equal to "{at1}"
    And the JSON node "data[0].type" should be equal to "{atype1}"
    And the JSON node "data[0].name" should be equal to "Templ 1"
    And the JSON node "data[0].description" should be equal to "Approval Templ 1"
    And the JSON node "data[0].required_approvals" should be equal to "1"
    And the JSON node "data[0].required_rejections" should be equal to "4"
    And the JSON node "data[0].can_approvers_view_subject" should be true
    And the JSON node "data[0].approver_criteria.agents" should have "1" element
    And the JSON node "data[1].id" should be equal to "{at2}"
    And the JSON node "data[1].type" should be equal to "{atype2}"
    And the JSON node "data[1].name" should be equal to "Templ 2"
    And the JSON node "data[1].description" should be equal to "Approval Templ 2"
    And the JSON node "data[1].required_approvals" should be equal to "2"
    And the JSON node "data[1].required_rejections" should be equal to "1"
    And the JSON node "data[1].can_approvers_view_subject" should be false
    And the JSON node "data[1].approver_criteria.agents" should have "2" elements

  Scenario: I PUT an existing approval template as user
    Given I'm authenticated as "user"
    When I send a PUT request to "/api/v2/approval_templates/{at1}"
    Then the response status code should be 403

  Scenario: I PUT an existing approval template as agent
    Given I'm authenticated as "agent"
    When I send a PUT request to "/api/v2/approval_templates/{at1}"
    Then the response status code should be 403

  Scenario: I PUT a non-existing approval template as admin
    Given I'm authenticated as "admin"
    When I send a PUT request to "/api/v2/approval_templates/99999"
    Then the response status code should be 404

  Scenario: I PUT an existing approval template as admin
    Given I'm authenticated as "admin"
    And I send a GET request to "/api/v2/approval_templates/{at1}"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{at1}"
    And the JSON node "data.type" should be equal to "{atype1}"
    And the JSON node "data.required_approvals" should be equal to "1"
    And the JSON node "data.required_rejections" should be equal to "4"
    And the JSON node "data.can_approvers_view_subject" should be true
    And the JSON node "data.approver_criteria.agents" should have "1" element
    When I send a PUT request to "/api/v2/approval_templates/{at1}" with body:
            """
{
  "name": "Approval Template Delta",
  "description": "Approval template delta description",
  "type": ~atype2~,
  "required_approvals": 18,
  "required_rejections": 20,
  "can_approvers_view_subject": false,
  "approver_criteria": {
    "agents": [1, 2]
  }
}
            """
    Then the response status code should be 204
    And I send a GET request to "/api/v2/approval_templates/{at1}"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{at1}"
    And the JSON node "data.type" should be equal to "{atype2}"
    And the JSON node "data.required_approvals" should be equal to "18"
    And the JSON node "data.required_rejections" should be equal to "20"
    And the JSON node "data.can_approvers_view_subject" should be false
    And the JSON node "data.approver_criteria.agents" should have "2" elements

  Scenario: I PUT an existing approval template as admin and expect error when I cannot choose approvers and don't define any
    Given I'm authenticated as "admin"
    And I send a GET request to "/api/v2/approval_templates/{at1}"
    Then the response status code should be 200
    And the JSON node "data.id" should be equal to "{at1}"
    And the JSON node "data.type" should be equal to "{atype1}"
    And the JSON node "data.required_approvals" should be equal to "1"
    And the JSON node "data.required_rejections" should be equal to "4"
    And the JSON node "data.can_approvers_view_subject" should be true
    And the JSON node "data.approver_criteria.agents" should have "1" element
    When I send a PUT request to "/api/v2/approval_templates/{at1}" with body:
            """
{
  "name": "Approval Template Delta",
  "description": "Approval template delta description",
  "type": ~atype2~,
  "required_approvals": 18,
  "required_rejections": 20,
  "can_approvers_view_subject": false,
  "approver_criteria": {
    "can_choose_approvers": false,
    "agents": [],
    "users": []
  }
}
            """
    Then the response status code should be 400
    And the JSON node "errors.errors[0].message" should be equal to "There aren't enough approvers to meet the approval/rejection thresholds"
    And the JSON node "errors.errors[1].message" should be equal to "At least one approver criteria must be provided"
    And the JSON node "errors.errors[2].message" should be equal to "If an agent cannot choose approvers, then agent and/or user approvers must be supplied"

  Scenario: I try to DELETE an approval template without authentication
    When I send a DELETE request to "/api/v2/approval_templates/{at1}"
    Then the response status code should be 401

  Scenario: I DELETE an existing approval template as user
    Given I'm authenticated as "user"
    When I send a DELETE request to "/api/v2/approval_templates/{at1}"
    Then the response status code should be 403

  Scenario: I DELETE an existing approval template as agent
    Given I'm authenticated as "agent"
    When I send a DELETE request to "/api/v2/approval_templates/{at1}"
    Then the response status code should be 403

  Scenario: I DELETE an approval template as admin
    Given I'm authenticated as "admin"
    When I send a DELETE request to "/api/v2/approval_templates/{at1}"
    Then the response status code should be 200
    When I send a GET request to "/api/v2/approval_templates"
    Then the response status code should be 200
    And the JSON node "meta.pagination.total" should be equal to "1"

  Scenario: I count approval templates as an agent
    Given I'm authenticated as "agent"
    When I send a GET request to "/api/v2/approval_templates/counts"
    Then the response status code should be 200
    And the JSON node "data.count" should be equal to "2"

  Scenario: I count approval templates as a admin
    Given I'm authenticated as "admin"
    When I send a GET request to "/api/v2/approval_templates/counts"
    Then the response status code should be 200
    And the JSON node "data.count" should be equal to "2"
