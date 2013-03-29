<html>
<head>
    <title>ludoDB Request handler sample</title>
</head>
<body>
    <h2>Example 1: get request:</h2>
    <p>Click here to show data for <a href="Book/2/read">Book/2</a></p>
    <h2>Example 2: delete request:</h2>
    <p>Click here to delete <a href="Book/2/delete">Book/2/delete</a></p>
    <h2>Example 3: save request:</h2>
    <form action="./Book/save" method="post">
        <table>
            <tr>
                <td><label for="isbn">Isbn</label>:</td>
                <td><input type="text" name="isbn" id="isbn" value="978123400234"></td>
            </tr>
            <tr>
                <td><label for="title">Title</label>:</td>
                <td><input type="text" name="title" id="title" value="My book"></td>
            </tr>
            <tr>
                <td><label for="author1">Authors:(semicolon separated)</label>:</td>
                <td><textarea type="text" name="author" id="author1">Jane Johnson;John Johnson</textarea></td>
            </tr>
            <tr>
                <td><input type="submit" value="Save book"></td>
            </tr>
        </table>
    </form>
    <p>After save, open Book/&lt;id&gt; where id is the id you'll see in the JSON response,
    example:  <a href="Book/4/read">Book/4</p>
</body>
</html>