<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta content="IE=edge" http-equiv="X-UA-Compatible">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <title>Development usage example</title>
    </head>
    <body>
        <button id="getPostsBtn" type="button">
            Get All Posts
        </button>

        <button id="getPostBtn" type="button">
            Get Single Post
        </button>

        <button id="createPostBtn" type="button">
            Create New Post
        </button>

        <button id="updatePostBtn" type="button">
            Update Post
        </button>

        <button id="deletePostBtn" type="button">
            Delete Post
        </button>
        <hr>

        <fieldset disabled>
            <legend>Request Parameters :</legend>
            <p id="requestMethod"></p>
            <br>
            <p id="requestParams"></p>
        </fieldset>
        <fieldset disabled>
            <legend>Raw response :</legend>
            <p id="consoleOutput"></p>
            <br>
            <p id="statusCode"></p>
            <p id="statusMessage"></p>
        </fieldset>

        <script crossorigin="anonymous"
                integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
                src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
            $(() => {
                $.ajaxSetup({
                    url: './development/ajax_handler.php',
                    dataType: 'JSON',
                    complete: function (xhr, textStatus) {
                        var params = this.type === 'GET' ? this.url : this.data;
                        $.log(this.type, params, xhr.responseText, textStatus, xhr.status);
                    }
                });

                $.log = (method, request, data, statusMessage, statusCode) => {
                    $('#requestMethod').text(method);
                    $('#requestParams').text(request);
                    $('#consoleOutput').text(data);
                    $('#statusCode').text(statusCode);
                    $('#statusMessage').text(statusMessage);
                };

                $('#getPostsBtn').click(() => {
                    $.ajax({
                        type: 'GET',
                        data: {'handler': 'getPosts'}
                    });
                });

                $('#getPostBtn').click(() => {
                    $.ajax({
                        type: 'POST',
                        data: {'handler': 'getPost', 'id': 1112}
                    });
                });

                $('#createPostBtn').click(() => {
                    $.ajax({
                        type: 'POST',
                        data: {
                            'handler': 'createPost',
                            'id': '1',
                            'title': 'Title Post 1',
                            'username': 'Content Post 1',
                        }
                    });
                });

                $('#updatePostBtn').click(() => {
                    $.ajax({
                        type: 'PUT',
                        data: {
                            'handler': 'updatePost',
                            'id': '1',
                            'title': 'Title Post 1',
                            'username': 'Content Post 1',
                        }
                    });
                });

                $('#deletePostBtn').click(() => {
                    $.ajax({
                        type: 'DELETE',
                        data: {
                            'handler': 'removePost',
                            'id': '1',
                        }
                    });
                });
            });
        </script>
    </body>
</html>
