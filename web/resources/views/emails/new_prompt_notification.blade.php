<html>
  <body>
    <b>New prompt submitted</b><br>

    <b>Caption</b>: {{ $prompt->caption }}<br>
    <b>Option</b>: {{ $prompt->option0 }} / {{ $prompt->option1 }}<br><br>

    <b>Actions</b><br>
    <button style="padding-top:20px; border-width:3px; border-color:green; border-radius:5px; width:80%; font-size:20px; color:black">Approve</button><br>
    <button style="padding-top:20px; border-width:3px; border-color:red; border-radius:5px; width:80%; font-size:20px; color:black">Deny</button>
  </body>
</html>
