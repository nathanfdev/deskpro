Feature: Admin Tickets Labels page
	In order to manage labels
	As an administrator
	I need to add a label, edit and remove label

	@javascript
	Scenario: Loading tickets labels
		Given I am logged in as admin "admin@example.com"
		And I am on "/adm/#/tickets/labels"
		When admin page is loaded
		And admin sections are loaded
		Then I should see "Labels"
	
	@javascript
	Scenario: Create new label
		Given I am logged in as admin "admin@example.com"
		And I am on "/adm/#/tickets/labels/create"
		When admin page is loaded
		And admin sections are loaded
		And I fill in "label" with "Behat Label"
		And I press "Save"
		And admin sections are loaded
		And admin page is loaded
		Then I should see "Behat Label"
    		
	@javascript
	Scenario: Edit label
		Given I am logged in as admin "admin@example.com"
		And I am on "/adm/#/tickets/labels/Behat%20Label"
		When admin page is loaded
		And admin sections are loaded
		And I fill in "label" with "B-e-h-a-t L-a-b-e-l"
		And I press "Save"
		And admin sections are loaded
		And admin page is loaded
		Then I should see "B-e-h-a-t L-a-b-e-l"
		But I should not see "Behat Label"
