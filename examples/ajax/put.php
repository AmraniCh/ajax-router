<?php

return [
    'update-user' => function($userID, $email, $nickname) {
        return json_encode(['message' => "Updated user email to '$email', $nickname to '$nickname' for user with ID = '$userID'"]);
    }
];
