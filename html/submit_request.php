<?php
session_name("HHN");
session_start();

require_once __DIR__.'/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
  die(json_encode(array("error"=>true, "message"=>"Unauthorized.")));
}

if (isset($_POST["submit"]) && $_POST["submit"]=="submit" && isset($_POST["selections"])) {
  try {
    $submission = json_decode($_POST["selections"], true);
    $col_count = count($submission["group_by"] ?? []) + count($submission["aggregate"] ?? []);
    if ($col_count == 0) {
      die(json_encode(array("error"=>true, "message"=>"You must select at least one column.")));
    }
    if ($col_count > 8) {
      die(json_encode(array("error"=>true, "message"=>"You can select at most 8 columns.")));
    }
    $filename = preg_replace('/[^a-zA-Z0-9-_]/', '', $_POST["filename"] ?? (rand(1000000,9999999))).".txt";

    $connection = new AMQPStreamConnection('localhost', 5672, 'admin', 'b4294ebfa570c4be2d185472ca782bcf');
    $channel = $connection->channel();
    $channel->queue_declare('requests', false, true, false, false);
    $msg = new AMQPMessage(json_encode(array(
      "group_by" => $submission["group_by"],
      "aggregate" => $submission["aggregate"],
      "filename" => $filename,
      "report_by" => $_SESSION["first"]." ".$_SESSION["last"]
    )));
    $channel->basic_publish($msg, '', 'requests');
    $channel->close();
    $connection->close();

    die(json_encode(array("error"=>false, "message"=>'You request has been submitted. It may take a few seconds to get processed. Once generated, the report will be <a href="./reports/'.$filename.'" target="_blank">available here</a>.')));

  } catch(Exception $e) {
    die(json_encode(array("error"=>true, "message"=>"An unknown error has occurred.")));
  }
}
