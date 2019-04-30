Feature: Downloads
  Check Download EULA

  Background:
    Given the setting "user.attachment_require_auth_downloads" is set to "true"
    And the following languages are enabled:
      | default |
    And only the following custom download fields exist:
      | #              | Type          | SysName | Parent        | Title        |
      | eula_field     | single_choice | eula    |               | Eula field   |
      | eula_option1   |               |         | {eula_field}  | Eula option 1|
    And the "download" category "General" exists with content titled "Example Download"
    And the object "created_content" has "eula_field" custom data set to "{eula_option1}"

  Scenario: I download file and accept EULA
    Given I am on "/downloads/files/example-download"
    And the response status code should be 200
    When I follow "filename.txt"
    Then I should be on "/downloads/eula/{eula_option1}"
    And the response status code should be 200
    And I should see "Agree"
    When I press "Agree"
    Then I should be on "/downloads/files/example-download?start_download=1"
    And the response status code should be 200

    
