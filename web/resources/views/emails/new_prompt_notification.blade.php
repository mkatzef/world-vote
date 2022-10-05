<html>
  <body>
    <b>New prompt submitted</b><br>

    <b>Caption</b>: {{ $prompt->caption }}<br>
    <b>Options</b>: {{ $prompt->option0 }} / {{ $prompt->option1 }}<br><br>

    <b>Actions</b><br>
    <a href="https://myworld.vote/review/{{ $prompt->id }}/{{ $auth_code }}/approve">
      <button
        style="padding-top:20px; border-width:3px; border-color:green; border-radius:5px; width:80%; font-size:20px; color:black"
      >Approve</button><br>
    </a>
    <a href="https://myworld.vote/review/{{ $prompt->id }}/{{ $auth_code }}/deny">
      <button
      style="padding-top:20px; border-width:3px; border-color:red; border-radius:5px; width:80%; font-size:20px; color:black"
      >Deny</button>
    </a>
  </body>
</html>
