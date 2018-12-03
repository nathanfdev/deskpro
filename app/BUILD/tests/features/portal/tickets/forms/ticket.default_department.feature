@new
Feature: Check default department for brand

  Background:
    Given I have only default brand
    And I'm authenticated as user
    And the default brand is using the standard theme
    And the only default ticket layout exists with fields:
      | user_layout |
      | department  |
    And only the following Department records exist:
      | #  | Title        | Is Tickets Enabled | Brands           |
      | d1 | Department 1 | 1                  | [{defaultBrand}] |
      | d2 | Department 2 | 1                  | [{defaultBrand}] |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered

  Scenario: I check default department is set
    Given only setting for brand "{defaultBrand}" with name "default_department.user" and value "{d1}" exists
    And I go to "/new-ticket"
    Then the "Department" field should contain "{d1}"

  Scenario: I check default department is set but we respect get parameter
    Given only setting for brand "{defaultBrand}" with name "default_department.user" and value "{d1}" exists
    And I go to "/new-ticket?department_id={d2}"
    Then the "Department" field should contain "{d2}"
