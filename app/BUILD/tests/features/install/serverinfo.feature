Feature: Server Info
  In order for the installer to function properly, it needs special Server Info requests to work.

  Scenario: I request check_requirements
    Given I am on "/index.php?__serverinfo=check_requirements&auth=TEST"
    Then I should see "All checks passed successfully. Your system is ready to run DeskPRO."

  Scenario: I request check_requirements with encoded output
    Given I am on "/index.php?__serverinfo=check_requirements&auth=TEST&encode-output"
    Then the response should contain "-------------------------BEGIN-------------------------"
    And the response should contain "-------------------------END-------------------------"
    And the encoded output should decode into "DpSys\SoftwareRequirements\DeskproRequirements"

  Scenario: I request the check path page
    Given I am on "/__serverinfo/url_check/path?auth=TEST"
    Then the response should contain "DP_CHECK_SUCCESS"

  Scenario: I use an invalid auth token
    Given I am on "/__serverinfo/url_check/path?auth=WRONG_TOKEN"
    Then the response should contain "The auth code in the URL you are trying to view is invalid."
