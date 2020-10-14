<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css?family=Inconsolata"
          rel="stylesheet" type="text/css" />
    <style>
        ::selection {
            background: #FF5E99;
        }
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
        }
        body {
            font-size: 11pt;
            font-family: Inconsolata, monospace;
            color: white;
            background-color: black;
        }
        #container {
            padding: .1em 1.5em 1em 1em;
        }
        #container output {
            clear: both;
            width: 100%;
        }
        #container output h3 {
            margin: 0;
        }
        #container output pre {
            margin: 0;
        }
        .input-line {
            display: -webkit-box;
            -webkit-box-orient: horizontal;
            -webkit-box-align: stretch;
            display: -moz-box;
            -moz-box-orient: horizontal;
            -moz-box-align: stretch;
            display: -moz-box;
            box-orient: horizontal;
            box-align: stretch;
            clear: both;
        }
        .input-line > div:nth-child(2) {
            -webkit-box-flex: 1;
            -moz-box-flex: 1;
            box-flex: 1;
        }
        .prompt {
            white-space: nowrap;
            color: #96b38a;
            margin-right: 7px;
            display: -webkit-box;
            -webkit-box-pack: center;
            -webkit-box-orient: vertical;
            display: -moz-box;
            -moz-box-pack: center;
            -moz-box-orient: vertical;
            display: box;
            box-pack: center;
            box-orient: vertical;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }
        .cmdline {
            outline: none;
            background-color: transparent;
            margin: 0;
            width: 100%;
            font: inherit;
            border: none;
            color: inherit;
        }
        .ls-files {
            height: 45px;
            -webkit-column-width: 100px;
            -moz-column-width: 100px;
            -o-column-width: 100px;
            column-width: 100px;
        }

    </style>
    <title>Hello, world!</title>
</head>
<body>

{{content}}


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>

    $(function() {

        let prompt = document.getElementById("prompt");
        let outputElement = document.querySelector("#output");
        let cmdLine = document.querySelector("#cmdLine");

        let commandHistory = [];
        let historyPos = -1;
        setTimeout(function () {
            prompt.innerText = new Date() + " | Created by Darwin Marcelo"
            setInterval(()=>{
                let date = new Date();
                prompt.innerText =
                    `${date.getFullYear()}-${date.getMonth()}-${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()} | username@bull`;
            },1000)
        },2000);

        window.addEventListener("click",ev => {
            cmdLine.focus();
        });

        window.addEventListener("keyup",ev => {

            // if(historyPos < 0 || historyPos > commandHistory.length -1) return;

            if(ev.key === "ArrowUp" && historyPos < commandHistory.length-1)
            {
                historyPos++;
                cmdLine.value = commandHistory[historyPos];
            }

            if(ev.key === "ArrowDown" && historyPos > 0)
            {
                historyPos--;
                cmdLine.value = commandHistory[historyPos];
            }
        });

        $("#cmdForm").on("submit",function (e) {
            e.preventDefault();
            commandHistory.push(cmdLine.value);

            if(cmdLine.value.trim() === "")
            {
                return;
            }

            let promptElement = document.createElement("div");
            let prompt = document.querySelector("#prompt");
            promptElement.classList.add("prompt");
            promptElement.innerText = prompt.textContent;

            let cmdLineElement = document.createElement("input");
            cmdLineElement.classList.add("cmdline");
            cmdLineElement.value = cmdLine.value;
            cmdLineElement.readOnly = true;

            let divElement = document.createElement("div");
            divElement.appendChild(cmdLineElement);

            let inputLineDivElement = document.createElement("div");
            inputLineDivElement.classList.add("input-line");
            inputLineDivElement.appendChild(promptElement);
            inputLineDivElement.appendChild(divElement);

            outputElement.appendChild(inputLineDivElement);
            let paragraphElement = document.createElement("p");


            //Submit form
            $.ajax({
                type: 'POST',
                url: "./console",
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.messages)
                    {
                        response.messages.forEach(item=>{
                            let message = document.createElement("p");
                            message.innerHTML = item;
                            outputElement.appendChild(message);
                        });
                        return;
                    }
                    paragraphElement.innerHTML = response.message;
                    outputElement.appendChild(paragraphElement);
                },
                error: function (event) {
                    paragraphElement.innerHTML = `'<em>${cmdLine.value}</em>' is not recognized as internal command`;
                    outputElement.appendChild(paragraphElement);
                },
            }).always(t => {
                cmdLine.value = '';
            });

            window.scrollTo(0,getDocHeight())

        });

        function getDocHeight() {
            const d = document;
            return Math.max(
                Math.max(d.body.scrollHeight, d.documentElement.scrollHeight),
                Math.max(d.body.offsetHeight, d.documentElement.offsetHeight),
                Math.max(d.body.clientHeight, d.documentElement.clientHeight)
            );
        }

    });

</script>
</body>
</html>