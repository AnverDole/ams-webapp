<?php

$isFlowStarted = false;
function setFlowStarted()
{
    global $isFlowStarted;
    $isFlowStarted = true;
}
function checkFlowAccess()
{
    global $isFlowStarted;
    if(!$isFlowStarted){
        die("Direct access is not possible.");
    }
}
