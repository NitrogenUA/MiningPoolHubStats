<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
/**
 * Copyright (C) 2017  James Dimitrov (Jimok82)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *
 */

//DO NOT EDIT THIS FILE! EDIT CONFIG.PHP



require_once ("config.php");
require_once ("miningpoolhubstats.class.php");


//Check to see we have an API key. Show an error if none is defined.
if ($_GET['api_key'] != null) {
	$api_key = filter_var($_GET['api_key'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
}
if ($api_key == null || $api_key == "INSERT_YOUR_API_KEY_HERE" || strlen($api_key) <= 32) {
	die("Please enter an API key: example: " . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?api_key=ENTER_YOUR_KEY_HERE");
}

//Check to see what we are converting to. Default to USD
if ($_GET['fiat'] != null) {
	$fiat = filter_var($_GET['fiat'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
}
if ($fiat == "SET_FIAT_CODE_HERE" || strlen($fiat) >= 4) {
	$fiat = "USD";
}

//Check to see what we are converting to. Default to BTC
if ($_GET['crypto'] != null) {
	$crypto = filter_var($_GET['crypto'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
}
if ($crypto == "SET_CRYPTO_CODE_HERE" || strlen($crypto) >= 5) {
	$crypto = "ETH";
}

$mph_stats = new miningpoolhubstats($api_key, $fiat, $crypto);
$crypto_decimals = $mph_stats->get_decimal_for_conversion();


//GENERATE THE UI HERE
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Miner Stats</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <style>
        * {
            font-size: 9px;
            line-height: 1.0;
        }
    </style>
</head>
<body>
<script language="JavaScript">
    var timerInit = function (cb, time, howOften) {
        // time passed in is seconds, we convert to ms
        var convertedTime = time * 1000;
        var convetedDuration = howOften * 1000;
        var args = arguments;
        var funcs = [];

        for (var i = convertedTime; i > 0; i -= convetedDuration) {
            (function (z) {
                // create our return functions, within a private scope to preserve the loop value
                // with ES6 we can use let i = convertedTime
                funcs.push(setTimeout.bind(this, cb.bind(this, args), z));

            })(i);
        }

        // return a function that gets called on load, or whatever our event is
        return function () {

            //execute all our functions with their corresponsing timeouts
            funcs.forEach(function (f) {
                f();
            });
        };

    };

    // our doer function has no knowledge that its being looped or that it has a timeout
    var doer = function () {
        var el = document.querySelector('#timer');
        var previousValue = Number(el.innerHTML);
        if (previousValue == 1) {
            location.reload();
        } else {
            document.querySelector('#timer').innerHTML = previousValue - 1;
        }
    };


    // call the initial timer function, with the cb, how many iterations we want (30 seconds), and what the duration between iterations is (1 second)
    window.onload = timerInit(doer, 60, 1);
</script>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">MinerStats</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">Stats</a></li>
            </ul>
            <ul class="nav navbar-nav pull-right">
                <li>
                    <a id="timer" class="nav">60</a>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>
<div class="container"><br><br><br><br><br>
    <h1>MiningPoolHub Stats</h1>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-striped">
                <tr>
                    <th>Coin</th>
                    <th>Confirmed (% of min payout)</th>
                    <th>Unconfirmed</th>
                    <th>Total</th>
                    <th>Total in <?php echo $mph_stats->crypto; ?></th>
                    <th>Value (Conf.)</th>
                    <th>Value (Unconf.)</th>
                    <th>Value (Total)</th>
                </tr>
				<?php

				foreach ($mph_stats->coin_data as $coin) {
					?>
                    <tr>
                        <td>
                            <a target="_blank" href="https://<?php echo $coin->coin; ?>.miningpoolhub.com/index.php?page=account&action=pooledit"><span <?php if ($coin->confirmed >= $mph_stats->all_coins->{$coin->coin}->min_payout) {
									echo 'style="font-weight: bold; color: red;"';
								} ?> ><?php echo $coin->coin; ?></span></a></td>
                        <td><?php echo $coin->confirmed; ?><?php echo " (" . number_format(100 * $coin->confirmed / $mph_stats->all_coins->{$coin->coin}->min_payout, 0) . "%)"; ?></td>
                        <td <?php if (array_key_exists($coin->coin, $mph_stats->get_min_payout($coin->coin))) {
							echo 'class="info"';
						} ?>><?php echo $coin->unconfirmed; ?></td>
                        <td <?php if (array_key_exists($coin->coin, $mph_stats->get_min_payout($coin->coin))) {
							echo 'class="info"';
						} ?>><?php echo number_format($coin->confirmed + $coin->unconfirmed, $crypto_decimals); ?></td>
                        <td <?php if ($coin->unconfirmed_value > 0) {
							echo 'class="success"';
						} ?>><?php echo number_format($coin->confirmed_value_c + $coin->unconfirmed_value_c, 8) . " " . $crypto; ?></td>
                        <td <?php if ($coin->confirmed_value > 0) {
							echo 'class="success"';
						} ?>><?php echo number_format($coin->confirmed_value, $crypto_decimals) . " " . $fiat; ?></td>
                        <td <?php if ($coin->unconfirmed_value > 0) {
							echo 'class="success"';
						} ?>><?php echo number_format($coin->unconfirmed_value, $crypto_decimals) . " " . $fiat; ?></td>
                        <td <?php if ($coin->unconfirmed_value > 0) {
							echo 'class="success"';
						} ?>><?php echo number_format($coin->confirmed_value + $coin->unconfirmed_value, $crypto_decimals) . " " . $fiat; ?></td>
                    </tr>
					<?php
				}
				?>
                <tr>
                    <td>TOTAL</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><?php echo number_format($mph_stats->confirmed_total_c + $mph_stats->unconfirmed_total_c, 8) . " " . $crypto; ?></td>
                    <td><?php echo number_format($mph_stats->confirmed_total, $crypto_decimals) . " " . $fiat; ?></td>
                    <td><?php echo number_format($mph_stats->unconfirmed_total, $crypto_decimals) . " " . $fiat; ?></td>
                    <td><?php echo number_format($mph_stats->confirmed_total + $mph_stats->unconfirmed_total, $crypto_decimals) . " " . $fiat; ?></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-striped">
                <tr>
                    <th>Worker</th>
                    <th>Coin</th>
                    <th>Hashrate</th>
                    <th>Monitor</th>
                </tr>
				<?php foreach ($mph_stats->worker_data as $worker) { ?>
                    <tr>
                        <td>
                            <A target="_blank" HREF="https://<?php echo $worker->coin; ?>.miningpoolhub.com/index.php?page=account&action=workers"><?php echo $worker->username; ?></A>
                        </td>
                        <td><?php echo $worker->coin; ?></td>
                        <td><?php echo number_format($worker->hashrate, 2); ?></td>
                        <td><?php echo $worker->monitor == 1 ? "Enabled" : "Disabled"; ?></td>
                    </tr>
				<?php } ?>
            </table>
        </div>
    </div>
</body>
