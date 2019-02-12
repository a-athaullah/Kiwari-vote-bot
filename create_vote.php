<?php

?>
<html>
    <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
        input[type=text], select, textarea{
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        resize: vertical;
        }

        /* Style the label to display next to the inputs */
        label {
        padding: 12px 12px 12px 0;
        display: inline-block;
        }

        /* Style the submit button */
        input[type=submit] {
        background-color: #4CAF50;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        float: right;
        margin-top: 6px;
        }

        /* Style the container */
        .container {
        border-radius: 5px;
        background-color: #f2f2f2;
        padding: 20px;
        }

        /* Floating column for labels: 25% width */
        .col-25 {
        float: left;
        width: 25%;
        margin-top: 6px;
        }

        /* Floating column for inputs: 75% width */
        .col-75 {
        float: left;
        width: 75%;
        margin-top: 6px;
        }
        .col-100 {
        float: left;
        width: 100%;
        margin-top: 6px;
        }
        /* Clear floats after the columns */
        .row:after {
        content: "";
        display: table;
        clear: both;
        }

        /* Responsive layout - when the screen is less than 600px wide, make the two columns stack on top of each other instead of next to each other */
        @media screen and (max-width: 600px) {
        .col-25, .col-75, input[type=submit] {
            width: 100%;
            margin-top: 0;
        }
        }
        </style>
        <script>
            var optCount = 1;
            function addInput(){
                optCount ++;
                var input = document.createElement("INPUT");
                input.setAttribute("type", "text");
                input.setAttribute("name","option[]")
                input.setAttribute("placeholder","Option "+optCount);
                document.getElementById("optionArea").appendChild(input); 
            }
        </script>
    </head>
    <body>
    <div class="container">
        <form action='create.php' method='POST'>
            <input type='hidden' name='roomid' value='<? echo $_GET["roomId"]; ?>'/>
            <div class="row">
                <div class="col-25">
                    <label for="title">Title</label>
                </div>
                <div class="col-75">
                    <input type="text" id="fname" name="title" placeholder="Vote Title..">
                </div>
            </div>
            <div class="row">
                <div class="col-25">
                    <label for="desc">Description</label>
                </div>
                <div class="col-75">
                    <textarea id="subject" name="desc" placeholder="Vote Description.." style="height:200px"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-25" style="width:100%">
                    <label for="lname">Option</label>
                    <input id = 'addButton' type='button' onclick='addInput();' value='+'/>
                </div>
            <div class="col-100" id='optionArea'>
                <input type="text" name="option[]" placeholder="Option 1">
            </div>
            </div>
            
            
            <div class="row" style = "margin-top:6px;">
            <input type="submit" value="Submit">
            </div>
        </form>
    </div>
    </body>
</html>