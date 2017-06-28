@new
Feature: Registration defaults from query params

  Background:
    Given I have only default brand
    And the following languages are enabled:
      | default |
    And the default brand is using the standard theme

  Scenario: I open /register page with defaults
    Given I go to "/register?person_registration%5Bname%5D=User+Name&person_registration%5Bprimary_email%5D%5Bemail%5D=user_1%40deskpro.dev"
    Then the "person_registration[name]" field should contain "User Name"
    And the "person_registration[primary_email][email]" field should contain "user_1@deskpro.dev"
