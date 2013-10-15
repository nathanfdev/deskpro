Feature: Admin Tickets Labels page
	In order to manage labels
	As an administrator
	I need to add a label, edit and remove label

	@javascript
	Scenario: Loading tickets labels
		Given I am logged in as admin "admin@example.com"
		Given I am on "/adm/#/tickets/labels"
		When admin page is loaded
		And admin sections are loaded
		Then I should see "Labels"
	
	@javascript
	Scenario: Create new label
		Given I am logged in as admin "admin@example.com"
		Given I am on "/adm/#/tickets/labels/create"
		When admin page is loaded
		And admin sections are loaded
		And I fill in "label" with "Behat Label"
		And I press "Save"
		Then I should see "Label Behat Label was saved successfully"
          