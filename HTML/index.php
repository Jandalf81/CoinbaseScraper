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
            google.charts.load('current', {'packages':['table', 'corechart'], 'language':'de'});
            google.charts.setOnLoadCallback(drawCharts);

            function drawCharts() {
                drawAccountSnapshot();
                drawAccountDevelopment();
            }

            function drawAccountSnapshot() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Code');
                data.addColumn('string', 'Name')
                data.addColumn('number', 'Units');
                data.addColumn('number', 'UnitPrice');
                data.addColumn('number', 'Value');
                data.addRows([
<?php
    $results = $db->query('SELECT * FROM snapshots ORDER BY timestamp DESC LIMIT 1');
    while ($row = $results->fetchArray()) {
        foreach($currencies as $currency) {
            echo "\t\t\t\t\t['" . $currency . "', 'name', " . $row['units' . $currency] . ', ' . $row['unitPrice' . $currency] . ', ' . $row['sum' . $currency] . '],' . $rn;
        }
    }
?>
                ]);

                var fCurrency = new google.visualization.NumberFormat({
                    fractionDigits: 5,
                    suffix: ' €'
                });

                var fFloat = new google.visualization.NumberFormat({
                    fractionDigits: 5
                });

                // format the SUM
                fFloat.format(data, 2);
                fCurrency.format(data, 3);
                fCurrency.format(data, 4);

                var table = new google.visualization.Table(document.getElementById('divAccountSnapshot'));
                table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
            }

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
        # add new entry
        echo "\t\t\t\t\t[new Date(" . $row['timestamp'] . '), ' . $row['sum'];

        # add each currency
        foreach($currencies as $currency) {
            echo ', ' . $row[$currency];
        }

        echo '],' . $rn;
    }
?>
                ]);

                // set options for chart
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

                // prepare formatter
                var fCurrency = new google.visualization.NumberFormat({
                    //fractionDigits: 5,
                    //suffix: ' €',
                    pattern: '#,###.##### €'
                });

                // format the SUM
                fCurrency.format(data, 0);
<?php
    # format each currency
    foreach($currencies as $key => $currency) {
        echo "\t\t\t\tfCurrency.format(data, " . ($key + 1) . ');' . $rn;
    }
?>

                // draw chart
                var chart = new google.visualization.LineChart(document.getElementById('divAccountDevelopment'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="divAccountSnapshot" class="AccountSnapshot"></div>
        <div id="divAccountDevelopment" class="AccountDevelopment"></div>
    </body>
</html>