<?php
//https://github.com/paulhodel/php-sockets-multiple-connections-non-blocking
//https://www.php.net/manual/en/sockets.examples.php

ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Singapore');
// Set time limit to indefinite execution
set_time_limit(0);

// Set the ip and port we will listen on
$address = '0.0.0.0';
$port = 22226;

echo "[" . date("Y-m-d h:i:sa") . "] Server started $address:$port\n";

ob_implicit_flush();

// Create a TCP Stream socket
$sock = socket_create(AF_INET, SOCK_STREAM, 0);

// Bind the socket to an address/port
socket_bind($sock, $address, $port) or die('Could not bind to address');

// Start listening for connections
socket_listen($sock);

// Non block socket type
socket_set_nonblock($sock);

// Clients
$clients = [];
$clients_data = [];

$question_order = array("0-1-2","0-2-1","1-0-2","1-2-0","2-0-1","2-1-0"); //category order
$math_operation = array("add","substract","multiply","divide");
$word = array("food","aalim","eat","circuit","test","computer","drink","water","disk");
$word_cipher=array("reverse","rot13","reverse","rot13","atbash","reverse","atbash","rot13","atbash");
$general_question[0] = "DNS zone transfer occurs on port 53. (Of course you know that). But, it is TCP or UDP?";
$general_answer[0] = "TCP";
$general_question[1] = "I am not sure what does PuTTY means. Do you know what is TTY?";
$general_answer[1] = "teletype";
$general_question[2] = "Biggest port number possible";
$general_answer[2] = "65535";

// Loop continuously
while (true) {
    // Accept new connections
    if ($newsock = socket_accept($sock)) {
        if (is_resource($newsock)) {
            // Write something back to the user
            // socket_write($newsock, "\n", 2).chr(0);
            // Non bloco for the new connection
            socket_set_nonblock($newsock);
            // Do something on the server side
            socket_getpeername($newsock,$client_ip_new);
            echo "[" . date("Y-m-d h:i:sa") . "] New client connected. $client_ip_new\n";
            $msg = "\n[" . date("Y-m-d h:i:sa") . "] You are to answer 3 question in 4 seconds.\nAny incorrect attempt will require you to again.\nIf not sure, just answer in small letter.\nThis server will be down at every hour for 5 minutes. (ie: It will be inaccessible at 6am, 8pm, 11pm, for 5 minutes.\n\nType 'ok' to proceed, or 'quit' to end.\n\n";
            socket_write($newsock, $msg, strlen($msg));
            // Append the new connection to the clients array
            $clients[] = $newsock;
            $clients_data[$newsock]["ip"] = $client_ip_new;
            $clients_data[$newsock]["status"] = 0;
            $clients_data[$newsock]["connected_time"] = date("Y-m-d h:i:sa");
            $clients_data[$newsock]["question_set"] = 0;
        }
    }

    // Polling for new messages
    if (count($clients)) {
        foreach ($clients AS $k => $v) {
            // Check for new messages

            $client_ip = $clients_data[$v]["ip"];
            $string = '';
            if ($char = socket_read($v, 1024)) {
                $string .= $char;
            }
            // New string for a connection
            if ($string) {

                if (trim($string) == "quit") {
                    $msg = "[" . date("Y-m-d h:i:sa") . "] Bye!\n";
                    socket_write($clients[$k], $msg, strlen($msg));
                    echo "[" . date("Y-m-d h:i:sa") . "] [$k $client_ip] Client quit. Remaining " . count($clients) . " client(s)\n";
                    socket_close($clients[$k]);
                    unset($clients[$k]);
                }

                elseif (trim($string) == "ok" && ($clients_data[$v]["status"] == 0)) {

                    if ($clients_data[$v]["question_set"] == 0) {

                        //category 0
                        $math_question_operation = array_rand($math_operation);
                        $math_question_operation_no1 = rand(723,98721);
                        $math_question_operation_no2 = rand(564,89123);

                        if ($math_question_operation_no1 < $math_question_operation_no2) {
                            $math_question_operation_no_big = $math_question_operation_no2;
                            $math_question_operation_no_small = $math_question_operation_no1;
                        }
                        else {
                            $math_question_operation_no_big = $math_question_operation_no1;
                            $math_question_operation_no_small = $math_question_operation_no2;
                        }

                        switch ($math_operation[$math_question_operation]) {
                            case 'add':
                                $math_question_desc = "Can you add $math_question_operation_no1 to $math_question_operation_no2?";
                                $math_question_answer = $math_question_operation_no1 + $math_question_operation_no2;
                                break;
                            case 'substract':
                                $math_question_desc = "Given $math_question_operation_no_big - $math_question_operation_no_small = x and y=2+x. Find y.";
                                $math_question_answer = 2+($math_question_operation_no_big - $math_question_operation_no_small);
                                break;
                            case 'multiply':
                                $math_question_desc = "Multiply $math_question_operation_no1 and $math_question_operation_no2.";
                                $math_question_answer = $math_question_operation_no1 * $math_question_operation_no2;
                                break;
                            case 'divide':
                                $math_question_desc = "Divide $math_question_operation_no_big with $math_question_operation_no_small. Round to the nearest whole number.";
                                $math_question_answer = round($math_question_operation_no_big / $math_question_operation_no_small);
                                break;
                        }

                        //category 1
                        $word_question_no = array_rand($word);
                        $word_question_word = $word[$word_question_no];
                        $word_question_cipher = $word_cipher[$word_question_no];

                        switch ($word_question_cipher) {
                            case 'reverse':
                                $word_question_output = strrev($word_question_word);
                                $word_question_desc = "Reverse of $word_question_output is ...\n";
                                $word_question_answer = $word_question_word;
                                break;
                            case 'rot13':
                                $word_question_output = str_rot13($word_question_word);
                                $word_question_desc = "Shifted by 13, and we got this $word_question_output\n.";
                                $word_question_answer = $word_question_word;
                                break;
                            case 'atbash':
                                $word_question_output = atbash($word_question_word);
                                $word_question_desc = "After applying a monoalphabetic cipher, the string become $word_question_output \n.";
                                $word_question_answer = $word_question_word;
                                break;
                        }

                        //category 2
                        $general_question_no = rand(0,2);
                        $general_question_desc = $general_question[$general_question_no];
                        $general_question_answer = $general_answer[$general_question_no];

                        $question_selected[0] = $math_question_desc;
                        $answer_selected[0] = $math_question_answer;
                        $question_selected[1] = $word_question_desc;
                        $answer_selected[1] = $word_question_answer;
                        $question_selected[2] = $general_question_desc;
                        $answer_selected[2] = $general_question_answer;

                        //pick category order
                        $question_order_selected = array_rand($question_order);
                        $clients_data[$v]["question_order"] = $question_order[$question_order_selected];

                        $question_order_arr = explode("-",$clients_data[$v]["question_order"]);

                        $clients_data[$v]["q1"] = $question_selected[$question_order_arr[0]];
                        $clients_data[$v]["a1"] = $answer_selected[$question_order_arr[0]];
                        $clients_data[$v]["q2"] = $question_selected[$question_order_arr[1]];
                        $clients_data[$v]["a2"] = $answer_selected[$question_order_arr[1]];
                        $clients_data[$v]["q3"] = $question_selected[$question_order_arr[2]];
                        $clients_data[$v]["a3"] = $answer_selected[$question_order_arr[2]];

                    }

                    $clients_data[$v]["timestart"] = time();
                    $clients_data[$v]["currentq"] = 1;

                    $msg = "\n[" . date("Y-m-d h:i:sa") . "] Question No 1\n";
                    socket_write($clients[$k], $msg, strlen($msg));

                    $msg = "> " . $clients_data[$v]["q1"] . "\n\n";
                    socket_write($clients[$k], $msg, strlen($msg));

                    echo "[" . date("Y-m-d h:i:sa") . "] [$k $client_ip] Client ok. 1st question sent\n";
                    $clients_data[$v]["status"] = 1;
                }

                else {
                    if ($clients_data[$v]["status"] == 0) {
                        $msg = "\n[" . date("Y-m-d h:i:sa") . "] 'ok' or 'quit'?\n";
                        socket_write($clients[$k], $msg, strlen($msg));
                    }
                    else {
                        $msg = "\n[" . date("Y-m-d h:i:sa") . "] You answered " . trim($string) . " for question no " . trim($clients_data[$v]["currentq"]) . "\n";
                        $current_answer = "a" . $clients_data[$v]["currentq"];

                        if (trim($string) == ($clients_data[$v]["$current_answer"])) {
                            $clients_data[$v]["lastansweredtime"] = time();
                            $msg .= "CORRECT!\n\n";
                            socket_write($clients[$k], $msg, strlen($msg));
                            echo "[" . date("Y-m-d h:i:sa") . "] [$k $client_ip] answered correctly for question no " . trim($clients_data[$v]["currentq"]) . "\n";

                            $clients_data[$v]["lastansweredtime"] = time();

                            if (($clients_data[$v]["lastansweredtime"] - $clients_data[$v]["timestart"]) > 5) { //time limit
                                $msg = "But, time is up. Try again, faster!  \n\n";
                                socket_write($clients[$k], $msg, strlen($msg));
                                echo "[" . date("Y-m-d h:i:sa") . "] [$k $client_ip] Client time is up. Remaining " . count($clients) . " client(s)\n";
                                socket_close($clients[$k]);
                                unset($clients[$k]);   
                            }

                            elseif ($clients_data[$v]["currentq"] == 3) {  //maximum of 3 questions
                                $msg = "Great! You solved within the time limit. The flag is flag{flagflagflagflagflagflagflag}\n\nClosing connection. \n\n";
                                socket_write($clients[$k], $msg, strlen($msg));
                                echo "[" . date("Y-m-d h:i:sa") . "] [$k $client_ip] Client completed within time limit. Remaining " . count($clients) . " client(s)\n";
                                socket_close($clients[$k]);
                                unset($clients[$k]);      
                            }

                            elseif ($clients_data[$v]["currentq"] < 3) { 
                                $clients_data[$v]["currentq"] +=1 ;
                                $msg = "\n[" . date("Y-m-d h:i:sa") . "] Question No " . $clients_data[$v]["currentq"] . "\n";
                                socket_write($clients[$k], $msg, strlen($msg));
                                $nextq = "q" . $clients_data[$v]["currentq"];
                                $msg = "> " . $clients_data[$v][$nextq] . "\n\n";
                                socket_write($clients[$k], $msg, strlen($msg));
                                echo "[" . date("Y-m-d h:i:sa") . "] [$k $client_ip] Client ok. $nextq question sent\n";
                            }

                        }
                        else {
                            $msg .= "INCORRECT!\n\nPlease try again. Closing connection.\n\n";
                            //$msg .= $clients_data[$v]["$current_answer"] . "\n\n";
                            socket_write($clients[$k], $msg, strlen($msg));
                            echo "[" . date("Y-m-d h:i:sa") . "] [$k $client_ip] Disconnected for incorrect answer for question no " . trim($clients_data[$v]["currentq"]) . "\n";
                            socket_close($clients[$k]);
                            unset($clients[$k]);
                        }



                        // if (trim($string) == "conntime") {
                        //     $msg = "[" . date("Y-m-d h:i:sa") . "] Connected at" . $clients_data[$v]["connected_time"] . "\n";
                        //     socket_write($clients[$k], $msg, strlen($msg));
                        //     echo "[" . date("Y-m-d h:i:sa") . "] [$k $client_ip] time\n";
                        // }
                    }
                }
                //echo "$k : $string\n";

            } else {
                if ($seconds > 60) {
                    // Ping every 5 seconds for live connections
                    $msg .= "Please reconnect.\n\n";
                    //$msg .= $clients_data[$v]["$current_answer"] . "\n\n";
                    socket_write($clients[$k], $msg, strlen($msg));
                    echo "[" . date("Y-m-d h:i:sa") . "] [$k $client_ip] Disconnected. 60seconds limit\n";
                    socket_close($clients[$k]);
                    unset($clients[$k]);
                    // if (false === socket_write($v, 'Are you ok there?\n')) {
                    //     // Close non-responsive connection
                    //     socket_close($clients[$k]);
                    //     // Remove from active connections array
                    //     unset($clients[$k]);
                    // }
                    // Reset counter
                    $seconds = 0;
                }
            }
        }
    }

    sleep(1);

    $seconds++;
}

// Close the master sockets
socket_close($sock);

function atbash($string) //https://github.com/exercism/php/blob/master/exercises/atbash-cipher/example.php
{
    $a_z = range('a', 'z');
    $z_a = range('z', 'a');
    $string = preg_replace("/[^a-z0-9]+/", "", strtolower($string));
    $len = strlen($string);

    $count = 0;
    $encoded = [];
    foreach (str_split($string) as $char) {
        $count++;
        if (is_numeric($char)) {
            $encoded[] = $char;
        }
        if (ctype_lower($char)) {
            $encoded[] = $z_a[array_search($char, $a_z)];
        }
        if ($count % 5 == 0 && $count < $len) {
            $encoded[] = ' ';
        }
    }

    return implode('', $encoded);
}
