<!DOCTYPE html>
<?php
    $rn = "\r\n";

    # define path to database
    $db = new SQLite3('/home/pi/CoinbaseScraper/db.sqlite');

    # get list of all currencies for later use
    $results = $db->query('SELECT * FROM currencies ORDER BY code');
    while ($row = $results->fetchArray()) {
        $currencies[] = $row['code'];
    }
?>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>CoinbaseScraper</title>

        <link rel="stylesheet" href="generic.css">

        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load('current', {'packages':['corechart'], 'language':'de'});
            google.charts.setOnLoadCallback(drawAccountDevelopment);

            function drawAccountDevelopment() {
                var data = google.visualization.arrayToDataTable([
<?php
    # begin definition of datatable
    echo "\t\t\t\t\t['Timestamp', 'SUM'";

    # begin SQL statement
    $sql = 'SELECT (timestamp * 1000) [timestamp], [sum]';

    foreach ($currencies as $currency) {
        # extend definition of datatable
        echo ", '" . $currency . "'";

        # extend SQL statement
        $sql .= ', sum' . $currency . ' [' . $currency . ']';
    }

    # finalize definition of datatable
    echo '],' . $rn;

    # finalize SQL statement
    $sql .= ' FROM snapshots ORDER BY timestamp';

    # query database
    $results = $db->query($sql);
    while ($row = $results->fetchArray()) {
        echo "\t\t\t\t\t[new Date(" . $row['timestamp'] . '), ' . $row['sum'];

        foreach($currencies as $currency) {
            echo ', ' . $row[$currency];
        }

        echo '],' . $rn;
    }
?>
                ]);

                var options = {
                    title: 'Account Development',
                    curveType: 'none',
                    lineWidth: 1,
                    pointSize: 2,
                    legend: { 
                        position: 'right' 
                    },
                    hAxis: {
                        title: 'Timestamp'
                    },
                    vAxis: {
                        format: 'currency',
                        title: 'Value'
                    },
                    chartArea: {
                        left: 100,
                        top: 30,
                        right: 150,
                        bottom: 50
                    },
                    explorer: {
                        keepInBounds: true,
                        maxZoomOut: 1
                    },
                    series: {
                        0: {
                            color: '#000000',
                            lineWidth: 2,
                            pointSize: 3
                        }
                    }
                };

                var chart = new google.visualization.LineChart(document.getElementById('divAccountDevelopment'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="divAccountDevelopment" class="AccountDevelopment"></div>
    </body>
</html>