@new
Feature: /articles, /news, /downloads endpoints
  I want to check sideloading

  Background:
    Given I'm authenticated as admin

  Scenario Outline: I sideload content brands
    Given only the following Brand records exist:
      | #  | Name    |
      | b1 | Brand 1 |
    And only the following <category_entity> records exist:
      | #  | Title      | Brand |
      | c1 | Category 1 | {b1}  |
      | c2 | Category 2 | {b1}  |
    And only the following <entity> records exist:
      | #  | Title   | <category_prop> |
      | a1 | Title 1 | {c1}            |
      | a2 | Title 2 | {c2}            |

    When I send a GET request to "/api/v2/<endpoint>?include=brand,<category_include_prop>"
    Then the response status code should be 200
    And the JSON node "linked.<category_include_prop>" should exist
    And the JSON node "linked.<category_include_prop>.{c1}" should exist
    And the JSON node "linked.<category_include_prop>.{c2}" should exist
    And the JSON node "linked.brand" should exist
    And the JSON node "linked.brand.{b1}" should exist

    Examples:
      | endpoint  | category_entity  | entity   | category_prop | category_include_prop |
      | articles  | ArticleCategory  | Article  | To Category   | article_category      |
      | news      | NewsCategory     | News     | Category      | news_category         |
      | downloads | DownloadCategory | Download | Category      | download_category     |

  Scenario Outline: I sideload content category brands
    Given only the following Brand records exist:
      | #  | Name    |
      | b1 | Brand 1 |
    And only the following <category_entity> records exist:
      | #  | Title      | Brand |
      | c1 | Category 1 | {b1}  |
      | c2 | Category 2 | {b1}  |

    When I send a GET request to "/api/v2/<endpoint>_categories?include=brand"
    Then the response status code should be 200
    And the JSON node "linked.brand" should exist
    And the JSON node "linked.brand.{b1}" should exist

    Examples:
      | endpoint | category_entity  |
      | article  | ArticleCategory  |
      | news     | NewsCategory     |
      | download | DownloadCategory |
