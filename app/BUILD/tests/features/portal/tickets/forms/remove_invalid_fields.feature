@new
Feature: I check removing invalid empty choice fields from layout

  Background:
    Given no Person records exist
    And I have only default brand
    And I'm authenticated as user
    And user has defaultBrand brand
    And I disable anti-abuse rate limiting
    And I set permission "tickets.use" = 1 for registered usergroup
    And only the following Department records exist:
      | #  | Title        | Brands           | Is Tickets Enabled |
      | d1 | Department 1 | [{defaultBrand}] | 1                  |
      | d2 | Department 2 | [{defaultBrand}] | 1                  |
    And I grant the "{d1}" department permission of tickets app for usergroup registered
    And I grant the "{d2}" department permission of tickets app for usergroup registered

  Scenario Outline: I check required ticket field with no options
    Given only the following custom ticket fields exist:
      | #  | Type   | Parent | Title        | Options            |
      | t  | <type> |        | Custom field | {"required": true} |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | ticket_field_{t} |

    When I go to "/new-ticket"
    And I select "Department 1" from "Department"
    And I fill in "Subject" with "This is a subject"
    And I fill in "Message" with "Here is my ticket message"
    And I press "Submit"
    Then the url should match "/thank-you/[a-zA-Z0-9\-]+"
    And I should see a success flash message with the phrase "portal.flashes.ticket_created"

    When I go to "/new-ticket"
    And I press "Submit"

    Examples:
      | type           |
      | single_choice  |
      | multi_choice   |
      | checkbox_group |
      | radio_group    |

  Scenario Outline: I check required person field with no options
    Given only the following custom person fields exist:
      | # | Type   | Parent | Title        | Options            |
      | t | <type> |        | Custom field | {"required": true} |
    And the only default ticket layout exists with fields:
      | user_layout      |
      | user_field_{t} |

    When I go to "/new-ticket"
    And I select "Department 1" from "Department"
    And I fill in "Subject" with "This is a subject"
    And I fill in "Message" with "Here is my ticket message"
    And I press "Submit"
    Then the url should match "/thank-you/[a-zA-Z0-9\-]+"
    And I should see a success flash message with the phrase "portal.flashes.ticket_created"

    When I go to "/new-ticket"
    And I press "Submit"

    Examples:
      | type           |
      | single_choice  |
      | multi_choice   |
      | checkbox_group |
      | radio_group    |

  Scenario Outline: I check required org field with no options
    Given only the following custom organization fields exist:
      | # | Type   | Parent | Title        | Options            |
      | t | <type> |        | Custom field | {"required": true} |
    And the only default ticket layout exists with fields:
      | user_layout   |
      | org_field_{t} |

    When I go to "/new-ticket"
    And I select "Department 1" from "Department"
    And I fill in "Subject" with "This is a subject"
    And I fill in "Message" with "Here is my ticket message"
    And I press "Submit"
    Then the url should match "/thank-you/[a-zA-Z0-9\-]+"
    And I should see a success flash message with the phrase "portal.flashes.ticket_created"

    When I go to "/new-ticket"
    And I press "Submit"

    Examples:
      | type           |
      | single_choice  |
      | multi_choice   |
      | checkbox_group |
      | radio_group    |
