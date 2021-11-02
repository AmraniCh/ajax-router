<?php

return [
    'delete-user' => function($userID) {
        return json_encode(['message' => "User with ID = '$userID' removed successfully."]);
    }
];
