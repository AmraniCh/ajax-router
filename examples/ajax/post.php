<?php

return [
    'login' => function($id, $name) {
        return json_encode(['message' => "user with id='$id' and name='$name' is sign in successfully!"]);
    },
];
