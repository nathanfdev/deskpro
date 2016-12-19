@new
Feature: /articles, /news, /downloads endpoints
  To retrieve various sets of DeskPRO Articles/News/Downloads
  As an API user
  I want API endpoints with search capabilities

  Background:
    Given I'm authenticated as admin
    And agent and user exist
    And the only ArticleCategory has title equal to "Test" and referenced as "article_category"
    And the only NewsCategory has title equal to "Test" and referenced as "news_category"
    And the only DownloadCategory has title equal to "Test" and referenced as "download_category"
    And only the following Article records exist:
      | #  | Title           | Status    | Person  | To Category        |
      | a1 | Demo Article #1 | published | {admin} | {article_category} |
      | a2 | Demo Article #2 | published | {agent} | {article_category} |
      | a3 | Demo Article #3 | published | {agent} |                    |
    And only the following News records exist:
      | #  | Title        | Status    | Person  | Category        |
      | n1 | Demo News #1 | published | {admin} | {news_category} |
      | n2 | Demo News #2 | published | {agent} | {news_category} |
      | n3 | Demo News #3 | published | {agent} |                 |
    And only the following Download records exist:
      | #  | Title            | Status    | Person  | Category            |
      | d1 | Demo Download #1 | published | {admin} | {download_category} |
      | d2 | Demo Download #2 | published | {agent} | {download_category} |
      | d3 | Demo Download #3 | published | {agent} |                     |
    And I set permission "articles.use" = 1 for "registered" usergroup
    And I set permission "downloads.use" = 1 for "registered" usergroup
    And I set permission "news.use" = 1 for "registered" usergroup

  Scenario Outline: I search for content with empty search criteria
    When I send a GET request to "/api/v2/<endpoint>"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "meta.pagination.count" should be equal to 3
    And the JSON node "meta.pagination.current_page" should be equal to 1
    And the JSON node "meta.pagination.total_pages" should be equal to 1
    And the JSON node "meta.pagination.total" should be equal to 3
    And the JSON node "data[0].title" should contain "<title_substring>"

    Examples:
      | endpoint  | title_substring |
      | articles  | Demo Article #  |
      | news      | Demo News #     |
      | downloads | Demo Download # |

  Scenario Outline: I search for content filtering by status, hidden_status, author, category and period_created
    When I send a GET request to "/api/v2/<endpoint>?status=hidden&hidden_status=draft&author=1&category=1&period_created=ever"
    Then the response should be in JSON
    And the response status code should be 200

    Examples:
      | endpoint  |
      | articles  |
      | news      |
      | downloads |

  Scenario Outline: I GET records filtering by category
    When I send a GET request to "/api/v2/<endpoint>?sort=date_created&order=desc&category={<category_ref>}"
    Then the response should be in JSON
    And the response status code should be 200
    And the JSON node "data" should exist
    And the JSON node "meta" should exist
    And the JSON node "meta.pagination.total" should be equal to 2
    And the JSON node "meta.pagination.per_page" should be equal to 10
    And the JSON node "meta.pagination.total_pages" should be equal to 1

    Examples:
      | endpoint  | category_ref      |
      | articles  | article_category  |
      | news      | news_category     |
      | downloads | download_category |

  Scenario Outline: I filter by category brand
    Given only the following Brand records exist:
      | #  | Name    |
      | b1 | Brand 1 |
      | b2 | Brand 2 |
      | b3 | Brand 3 |
    And only the following <category_entity> records exist:
      | #  | Title      | Brand |
      | c1 | Category 1 | {b1}  |
      | c2 | Category 2 | {b1}  |
      | c3 | Category 3 | {b2}  |
      | c4 | Category 3 | NULL  |
    And only the following <entity> records exist:
      | #  | Title   | <category_prop> |
      | a1 | Title 1 | {c1}            |
      | a2 | Title 2 | {c1}            |
      | a3 | Title 3 | {c1}            |
      | a4 | Title 4 | NULL            |
      | a5 | Title 5 | {c2}            |
      | a6 | Title 6 | {c2}            |
      | a7 | Title 7 | {c3}            |
      | a8 | Title 8 | {c4}            |

    When I send a GET request to "/api/v2/<endpoint>"
    Then the response status code should be 200
    And the JSON node "data" should have 8 elements

    When I send a GET request to "/api/v2/<endpoint>?brands[]={b1}&brands[]={b2}"
    Then the response status code should be 200
    And the JSON node "data" should have 6 elements

    When I send a GET request to "/api/v2/<endpoint>?brands[]={b3}"
    Then the response status code should be 200
    And the JSON node "data" should have 0 elements

    Examples:
      | endpoint  | category_entity  | entity   | category_prop |
      | articles  | ArticleCategory  | Article  | To Category   |
      | news      | NewsCategory     | News     | Category      |
      | downloads | DownloadCategory | Download | Category      |

  Scenario Outline: I filter categories by brand
    Given only the following Brand records exist:
      | #  | Name    |
      | b1 | Brand 1 |
      | b2 | Brand 2 |
      | b3 | Brand 3 |
    And only the following <category_entity> records exist:
      | #  | Title      | Brand |
      | c1 | Category 1 | {b1}  |
      | c2 | Category 2 | {b1}  |
      | c3 | Category 3 | {b2}  |
      | c4 | Category 3 | NULL  |

    When I send a GET request to "/api/v2/<endpoint>_categories"
    Then the response status code should be 200
    And the JSON node "data" should have 4 elements

    When I send a GET request to "/api/v2/<endpoint>_categories?brands[]={b1}"
    Then the response status code should be 200
    And the JSON node "data" should have 2 elements

    When I send a GET request to "/api/v2/<endpoint>_categories?brands[]={b1}&brands[]={b2}"
    Then the response status code should be 200
    And the JSON node "data" should have 3 elements

    Examples:
      | endpoint | category_entity  |
      | article  | ArticleCategory  |
      | news     | NewsCategory     |
      | download | DownloadCategory |
