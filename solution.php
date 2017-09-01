<?php
// Ashton Hsu amh333@cornell.edu

/**
 * @param $request
 * @param $secret
 * @return bool|mixed
 */
function parse_request($request, $secret)
{
    $requestStrings = explode('.',strtr($request, '-_', '+/'));
    $signature = base64_decode($requestStrings[0]);
    $payload = base64_decode($requestStrings[1]);

    if(hash_hmac('sha256', $payload, $secret, true) == $signature) {
        return json_decode($payload,true);
    }

    return false;
}

/**
 * @param $pdo
 * @return int
 */
function total_number_of_valid_requests($pdo)
{
    $sql = "
    SELECT *
    FROM scores
  ";

    $statement = $pdo->prepare($sql);
    $statement->execute();
    $rows = $statement->fetchAll();

    return (count($rows));
}

/**
 * @param $pdo
 * @param $n
 * @return mixed
 */
function dates_with_at_least_n_scores($pdo, $n)
{
    $sql = "
    SELECT date
    FROM scores
    GROUP BY date
    HAVING COUNT(score) >= $n
    ORDER BY date DESC
  ";

    $statement = $pdo->prepare($sql);
    $statement->execute();
    $rows = $statement->fetchAll(PDO::FETCH_COLUMN);

    return ($rows);
}

/**
 * @param $pdo
 * @param $date
 * @return mixed
 */
function users_with_top_score_on_date($pdo, $date)
{
    $sql = "
    SELECT user_id
    FROM scores
    WHERE date = '$date'
    ORDER BY score DESC
    limit 3
  ";

    $statement = $pdo->prepare($sql);
    $statement->execute();
    $rows = $statement->fetchAll(PDO::FETCH_COLUMN);

    return ($rows);
}

/**
 * @param $pdo
 * @param $user_id
 * @return mixed
 */
function times_user_beat_overall_daily_average($pdo, $user_id)
{
    $sql = "
    SELECT Count(*)
    FROM (SELECT Avg(score) AS average, date
          FROM   scores
          GROUP  BY date) averages
        INNER JOIN (SELECT Avg(score) AS average, date
                     FROM   scores
                     WHERE  user_id = $user_id
                     GROUP  BY date) userAverages
        ON averages.date = userAverages.date
    WHERE  userAverages.average > averages.average
  ";

    $statement = $pdo->prepare($sql);
    $statement->execute();
    $total = $statement->fetchColumn();
    return ($total);
}
