<!DOCTYPE html>
<html>
<head>
    <title>New Comment on your Idea</title>
</head>
<body>
    <h2>Hello!</h2>
    <p>A new comment has been posted on your idea: <strong>{{ $idea->title }}</strong></p>

    <p><strong>Commented by:</strong> {{ $comment->isAnonymous ? 'Anonymous' : ($comment->staff ? $comment->staff->staffName : 'Unknown') }}</p>

    <br>
    <p><strong>Comment Content:</strong></p>
    <div>
        {{ $comment->comment }}
    </div>

    <br>
    <p>Please log in to the portal to view the full discussion or reply.</p>
</body>
</html>
