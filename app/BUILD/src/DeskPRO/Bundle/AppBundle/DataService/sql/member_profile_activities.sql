SELECT
    {columns}
FROM (
    (
        SELECT
            id,
            slug AS slug,
            "community_topic" AS type,
            date_created AS date,
            LEFT(title, :truncateDescription) AS description
        FROM
            community_topics
        WHERE
            person_id = :personId
        AND
            status <> 'hidden'
        ORDER BY
            id DESC
        LIMIT :subLimit
    )
    UNION
    (
        SELECT
            community_topics.id,
            community_topics.slug AS slug,
            "community_comment" AS type,
            community_topic_comments.date_created AS date,
            LEFT(community_topics.title, :truncateDescription) AS description
        FROM
            community_topic_comments
        INNER JOIN
            community_topics ON community_topic_comments.topic_id = community_topics.id AND community_topics.status <> 'hidden'
        WHERE
            community_topic_comments.person_id = :personId AND community_topic_comments.status = 'visible'
        ORDER BY
            community_topic_comments.id DESC
        LIMIT :subLimit
    )
    UNION
    (
        SELECT
            articles.id,
            articles.slug AS slug,
            "article_comment" AS type,
            article_comments.date_created AS date,
            LEFT(articles.title, :truncateDescription) AS description
        FROM
            article_comments
        INNER JOIN
            articles ON article_comments.article_id = articles.id
        WHERE
            article_comments.person_id = :personId AND article_comments.status = 'visible'
        ORDER BY
            article_comments.id DESC
        LIMIT :subLimit
    )
    UNION
    (
        SELECT
            downloads.id,
            downloads.slug AS slug,
            "download_comment" AS type,
            download_comments.date_created AS date,
            LEFT(downloads.title, :truncateDescription) AS description
        FROM
            download_comments
        INNER JOIN
            downloads ON download_comments.download_id = downloads.id
        WHERE
            download_comments.person_id = :personId AND download_comments.status = 'visible'
        ORDER BY
            download_comments.id DESC
        LIMIT :subLimit
    )
    UNION
    (
        SELECT
            news.id,
            news.slug AS slug,
            "news_comment" AS type,
            news_comments.date_created AS date,
            LEFT(news.title, :truncateDescription) AS description
        FROM
            news_comments
        INNER JOIN
            news ON news_comments.news_id = news.id
        WHERE
            news_comments.person_id = :personId AND news_comments.status = 'visible'
        ORDER BY
            news_comments.id DESC
        LIMIT :subLimit
    )
    UNION
    (
        SELECT
            topics.id,
            topics.slug AS slug,
            "topic_comment" AS type,
            topic_comments.date_created AS date,
            LEFT(topics.title, :truncateDescription) AS description
        FROM
            topic_comments
        INNER JOIN
            topics ON topic_comments.topic_id = topics.id
        WHERE
            topic_comments.person_id = :personId AND topic_comments.status = 'visible'
        ORDER BY
            topic_comments.id DESC
        LIMIT :subLimit
    )
    UNION
    (
        SELECT
            news.id,
            news.slug AS slug,
            CONCAT("news_rating_", IF(ratings.rating = 1, 'up', 'down')) AS type,
            ratings.date_created AS date,
            LEFT(news.title, :truncateDescription) AS description
        FROM
            ratings
        INNER JOIN
            news ON ratings.object_id = news.id AND ratings.object_type = 'news'
        WHERE
            ratings.person_id = :personId
        ORDER BY
            ratings.id DESC
        LIMIT :subLimit
    )
    UNION
    (
        SELECT
            community_topics.id,
            community_topics.slug AS slug,
            CONCAT("community_rating_", IF(ratings.rating = 1, 'up', 'down')) AS type,
            ratings.date_created AS date,
            LEFT(community_topics.title, :truncateDescription) AS description
        FROM
            ratings
        INNER JOIN
            community_topics ON ratings.object_id = community_topics.id AND ratings.object_type = 'community' AND community_topics.status <> 'hidden'
        WHERE
            ratings.person_id = :personId
        ORDER BY
            ratings.id DESC
        LIMIT :subLimit
    )
    UNION
    (
        SELECT
            articles.id,
            articles.slug AS slug,
            CONCAT("article_rating_", IF(ratings.rating = 1, 'up', 'down')) AS type,
            ratings.date_created AS date,
            LEFT(articles.title, :truncateDescription) AS description
        FROM
            ratings
        INNER JOIN
            articles ON ratings.object_id = articles.id AND ratings.object_type = 'article' AND articles.status <> 'hidden'
        WHERE
            ratings.person_id = :personId
        ORDER BY
            ratings.id DESC
        LIMIT :subLimit
    )
    UNION
    (
        SELECT
            downloads.id,
            downloads.slug AS slug,
            CONCAT("download_rating_", IF(ratings.rating = 1, 'up', 'down')) AS type,
            ratings.date_created AS date,
            LEFT(downloads.title, :truncateDescription) AS description
        FROM
            ratings
        INNER JOIN
            downloads ON ratings.object_id = downloads.id AND ratings.object_type = 'download' AND downloads.status <> 'hidden'
        WHERE
            ratings.person_id = :personId
        ORDER BY
            ratings.id DESC
        LIMIT :subLimit
    )
) AS t
{order}
{limit}
