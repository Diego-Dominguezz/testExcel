<?php
    if (isset($_POST['truckerid'])) {
        $json = array();
        require("db.php");
        $conn = $db;
        if ($conn->connect_error) {
            die($conn->connect_error);
        }

        $sql = $conn->query("SELECT * FROM `nom` WHERE `driverid` = '".$_POST['truckerid']."'") or trigger_error($conn->error);

        for ($i=0;$row = $sql->fetch_assoc(); $i++) {
            $json[$i]['id'] = $row['id'];
            $json[$i]['driverid'] = $row['driverid'];
            $json[$i]['millas'] = $row['millas'];
            $json[$i]['tarifa'] = $row['tarifa'];
            $json[$i]['tipopago'] = $row['tipopago'];
            $json[$i]['fechaup'] = $row['fechaup'];
        }

        echo json_encode($json);
    } else {
        echo "ISSET";
    }
