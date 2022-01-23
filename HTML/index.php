<!DOCTYPE html>
<?php
    $rn = "\r\n";

    # define path to database
    $db = new SQLite3('/home/pi/CoinbaseScraper/db.sqlite');

    class Currency {
        private $code;
        private $name;

        function __construct($code, $name) {
            $this->code = $code;
            $this->name = $name;
        }

        function get_code() {
            return $this->code;
        }

        function get_name() {
            return $this->name;
        }
    }

    # get list of all currencies for later use
    $listCurrencies = [];
    $results = $db->query('SELECT * FROM currencies ORDER BY code');
    while ($row = $results->fetchArray()) {
        $listCurrencies[] = new Currency($row['code'], $row['name']);
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
    # get latest snapshot
    $results = $db->query('SELECT * FROM snapshots ORDER BY timestamp DESC LIMIT 1');
    while ($row = $results->fetchArray()) {
        $sum = $row['sum'];

        # list values for each currency
        foreach($listCurrencies as $currency) {
            echo "\t\t\t\t\t['" . $currency->get_code() . "', '" . $currency->get_name() . "', " . $row['units' . $currency->get_code()] . ', ' . $row['unitPrice' . $currency->get_code()] . ', ' . $row['sum' . $currency->get_code()] . '],' . $rn;
        }
    }
    echo "\t\t\t\t\t['SUM', '', , , " . $sum . '],' . $rn;
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

                var options = {
                    alternatingRowStyle: true,
                    showRowNumber: true,
                    width: '100%',
                    cssClassNames: {
                        headerRow: 'GCheaderRow',
                        headerCell: 'GCheaderCell',
                        oddTableRow: 'GCoddTableRow',
                        tableRow: 'GCtableRow',
                        hoverTableRow: 'GChoverTableRow',
                        selectedTableRow: 'GCselectedTableRow',
                    }
                };

                var table = new google.visualization.Table(document.getElementById('divAccountSnapshot'));
                table.draw(data, options);
            }

            function drawAccountDevelopment() {
                var data = google.visualization.arrayToDataTable([
<?php
    # begin definition of datatable
    echo "\t\t\t\t\t['Timestamp', 'SUM'";

    # begin SQL statement
    $sql = 'SELECT (timestamp * 1000) [timestamp], [sum]';

    foreach ($listCurrencies as $currency) {
        # extend definition of datatable
        echo ", '" . $currency->get_code() . "'";

        # extend SQL statement
        $sql .= ', sum' . $currency->get_code() . ' [' . $currency->get_code() . ']';
    }

    # finalize definition of datatable
    echo '],' . $rn;

    # finalize SQL statement
    # TODO make timeframe selectable
    $sql .= ' FROM snapshots WHERE timestamp > ' . (time() - (60 * 60 * 24)) . ' ORDER BY timestamp';

    # query database
    $results = $db->query($sql);
    while ($row = $results->fetchArray()) {
        # add new entry
        echo "\t\t\t\t\t[new Date(" . $row['timestamp'] . '), ' . $row['sum'];

        # add each currency
        foreach($listCurrencies as $currency) {
            echo ', ' . $row[$currency->get_code()];
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
                    fractionDigits: 5,
                    suffix: ' €'
                });

                // format the SUM
                fCurrency.format(data, 0);
<?php
    # format each currency
    foreach($listCurrencies as $key => $currency) {
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