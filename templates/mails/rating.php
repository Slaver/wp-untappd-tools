<html lang="en">
<head>
    <title>Breweries' Rating</title>
</head>
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
            echo '<td>'.$b['rating_place'].'</td>';

            echo '<td>';
            if (!empty($b['yesterday'])) {
                if ($b['rating_place'] > $b['yesterday']['rating_place']) {
                    echo '<span style="color: red">↓</span> -'.($b['rating_place'] - $b['yesterday']['rating_place']);
                }
                if ($b['rating_place'] < $b['yesterday']['rating_place']) {
                    echo '<span style="color: green">↑</span>'.($b['yesterday']['rating_place'] - $b['rating_place']);
                }
            } else {
                echo '<strong style="color: orange">NEW!</strong>';
            }
            echo '</td>';

            echo '<td><a href="'.$b['brewery']['url'].'">'.$b['brewery']['title'].'</a></td>';

            echo '<td>'.round($b['rating'], 3).'</td>';
            echo '<td>';

            if (!empty($b['yesterday'])) {
                $diff = round($b['rating'] - $b['yesterday']['rating'], 4);
                if ($b['rating'] > $b['yesterday']['rating'] && $diff > 0.0001) {
                    echo ' <span style="color: green">↑</span> '.round($b['rating'] - $b['yesterday']['rating'], 4);
                }
                if ($b['rating'] < $b['yesterday']['rating'] && $diff > 0.0001) {
                    echo ' <span style="color: red">↓</span> -'.round($b['yesterday']['rating'] - $b['rating'], 4);
                }
            }
            echo '</td>';

            echo '<td>'.$b['beers'].'</td>';
            echo '<td>'.$b['ratings'].'</td>';
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