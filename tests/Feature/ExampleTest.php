<?php

it('returns a successful response from the welcome page', function () {
    $this->get('/')
        ->assertOk();
});
