<?php

declare(strict_types=1);

test('public registration is disabled and returns 404', function () {
    $this->get('/register')->assertNotFound();
});
