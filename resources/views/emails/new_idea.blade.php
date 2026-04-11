<!DOCTYPE html>
<html>

<head>
    <title>New Idea Submission</title>
</head>

<body>
    <h2>A new idea has been submitted!</h2>
    <p><strong>Title:</strong> {{ $idea->title }}</p>
    <p><strong>Submitted by:</strong>
        @if($idea->staff)
            {{ $idea->staff->staffName }}{{ $idea->isAnonymous ? ' (Anonymous)' : '' }}
        @else
            Unknown User
        @endif
    </p>

    <br>
    <p><strong>Description:</strong></p>
    <div>
        {!! nl2br(e($idea->description)) !!}
    </div>

    <br>
    <p>Please log in to the portal to review the idea, assign closure-settings if needed, or monitor activity.</p>
</body>

</html>