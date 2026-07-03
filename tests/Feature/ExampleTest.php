<?php

it('returns a successful response from the API root', function () {
    $this->get('/')
        ->assertOk();
});
