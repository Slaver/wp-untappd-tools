<html>
<head></head>
<body>
<?php
if ($report) {
    $i = 1;
    $sections = count($report);

    foreach ($report as $country => $data) {
        echo '<h4>'.$country.'</h4>';
        echo '<table><thead><tr><th>#</th><th style="width: 50px;"></th><th>Brewery</th><th>Rating</th><th style="width: 70px;"></th><th>Beers</th><th>Ratings</th></tr></thead><tbody>';

        foreach ($data as $b) {

            echo '<tr>';
            echo '<td>' . $b['rating_place'] . '</td>';

            echo '<td>';
            if (!empty($b['yesterday'])) {
                if ($b['rating_place'] > $b['yesterday']['rating_place']) {
                    echo '<span style="color: red">↓</span> -' . ($b['rating_place'] - $b['yesterday']['rating_place']);
                }
                if ($b['rating_place'] < $b['yesterday']['rating_place']) {
                    echo '<span style="color: green">↑</span>' . ($b['yesterday']['rating_place'] - $b['rating_place']);
                }
            } else {
                echo '<strong style="color: orange">NEW!</strong>';
            }
            echo '</td>';

            echo '<td><a href="' . $b['brewery']->url . '">' . $b['brewery']->title . '</a></td>';

            echo '<td>' . round($b['rating'], 3) . '</td>';
            echo '<td>';

            if (!empty($b['yesterday'])) {
                if (round($b['rating'], 3) > round($b['yesterday']['rating'], 3) && round($b['rating'] - $b['yesterday']['rating'], 3) > 0) {
                    echo ' <span style="color: red">↓</span> -' . round($b['yesterday']['rating'] - $b['rating'], 3);
                }
                if (round($b['rating'], 3) < round($b['yesterday']['rating'], 3) && round($b['yesterday']['rating'] - $b['rating'], 3) > 0) {
                    echo ' <span style="color: green">↑</span>' . round($b['rating'] - $b['yesterday']['rating'], 3);
                }
            }
            echo '</td>';

            echo '<td>' . $b['beers'] . '</td>';
            echo '<td>' . $b['ratings'] . '</td>';
            echo '</tr>';

        }
        echo '</tbody></table>';
        if ($i < $sections) {
            echo '<hr>';
        }
        $i++;
    }
}
?>
</body>
</html>