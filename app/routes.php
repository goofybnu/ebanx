<?php

$App->post('/reset', 'EbanxController:reset');
$App->get('/balance', 'EbanxController:balance');
$App->post('/event', 'EbanxController:event');
