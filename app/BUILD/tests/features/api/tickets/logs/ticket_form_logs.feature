@new
Feature: Ticket logs when using /api/v2/ticket_forms endpoint

  Background:
    Given I'm authenticated as admin
    And I have a Department record referenced as demo_department
    And I have the following TicketPriority records:
      | #          | Title      |
      | priority_1 | Priority 1 |
      | priority_2 | Priority 2 |
    And I have this Product records:
      | #         | Title     |
      | product_1 | Product 1 |
      | product_2 | Product 2 |
    And I add the following TicketCategory records:
      | #          | Title      |
      | category_1 | Category 1 |
      | category_2 | Category 2 |
    And I have the following TicketWorkflow records:
      | #          | Title      |
      | workflow_1 | Workflow 1 |
      | workflow_2 | Workflow 2 |
    And I have the following Ticket records:
      | #           | Subject     | Agent   | Priority     | Category     | Workflow     | Product     |
      | demo_ticket | Demo ticket | {admin} | {priority_1} | {category_1} | {workflow_1} | {product_1} |
    And agent@deskpro.com and user@deskpro.com exist

  @skip-ci
  # No ticket log found for: message_created,changed_subject,changed_person
  Scenario: I create a ticket and check its' logs
    When I send a POST request to "/api/v2/ticket_forms/agent" with body:
    """
{
  "subject": "Sample Ticket",
  "department": ~demo_department~,
  "message": {
    "message": "<p>my html message</p>",
    "format": "html"
  }
}
    """
    Then the "{demo_ticket}" ticket should have the following logs:
      | type               |
      | action_starter     |
      | ticket_created     |
      | message_created    |
      | changed_subject    |
      | changed_department |
      | changed_person     |

  @skip-ci
  # Unexpected field names: product, priority, category, workflow, followers, cc, fields
  Scenario: I modify a ticket and check its' logs
    Given I add a Department record and reference it as another_department
    And I add a Product and reference it as demo_product
    And I reset the "{demo_ticket}" ticket logs
    When I send a PUT request to "/api/v2/ticket_forms/agent/{demo_ticket}" with body:
    """
{
  "subject": "Modified subject",
  "department": ~another_department~,
  "product": ~demo_product~,
  "priority": ~priority_2~,
  "category": ~category_2~,
  "workflow": ~workflow_2~,
  "followers": ["agent@deskpro.com"],
  "cc": ["user@deskpro.com"],
  "labels": ["Label One", "Label Two"],
  "fields": {
    "5": "2016-02-09 17:28:00"
  }
}
    """
    Then the response status code should be 204
    Then the "{demo_ticket}" ticket should have the following logs:
      | type                       |
      | changed_department         |
      | changed_subject            |
      | changed_labels             |
      | changed_product            |
      | changed_priority           |
      | changed_category           |
      | changed_category           |
      | changed_workflow           |
      | changed_user_participants  |
      | changed_agent_participants |
      | changed_custom_field       |
