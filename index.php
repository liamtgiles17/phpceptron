<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>perceptron.php</title>
    </head>
    <style>
        #weights {
            background-color: #181818;
        }
    </style>
    <body>
        <script src="jquery.js"></script>
        <h1>PHP Perceptron Demo</h1>
        <p>Click canvas to re-train and test model.</p>
        <canvas id="weights" width="540" height="540"></canvas>
        <div><br/></div>
        <canvas id="scale" width="540" height="50"></canvas>
        <script>
            $(document).ready(() => {
                const canvas = document.getElementById("weights");
                const ctx = canvas.getContext("2d");
                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);

                const scaleCanvas = document.getElementById("scale");
                const scaleCtx = scaleCanvas.getContext("2d");
                const grad = scaleCtx.createLinearGradient(0, 0, 540, 0);
                grad.addColorStop(0, `rgb(${255-((60*2)+127)}, ${(60*2)+127}, ${255-((60*2)+127)})`);
                grad.addColorStop(1, `rgb(${255-((-60*2)+127)}, ${(-60*2)+127}, ${255-((-60*2)+127)})`);
                scaleCtx.fillStyle = grad;
                scaleCtx.fillRect(0, 0, 540, 30);
                scaleCtx.fillStyle = "rgb(0, 0, 0)";
                scaleCtx.font = "12px Serif";
                scaleCtx.fillText("60", 0, 50);
                scaleCtx.fillText("Weight", 250, 45);
                scaleCtx.fillText("-60", 522, 50);

                var WIDTH = 30;
                var HEIGHT = 30;
                var cellWidth = Math.floor(ctx.canvas.width/WIDTH);
                var cellHeight = Math.floor(ctx.canvas.height/HEIGHT);
                var trainAccuracy = 0;
                var testAccuracy = 0;

                function render(weights) {
                    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                    for (let i = 0; i < WIDTH; i++) {
                        for (let j = 0; j < HEIGHT; j++) {
                            let weight = weights[j][i];
                            weight *= 2;
                            weight += 127;
                            if (weight <= 0) weight = 0;
                            else if (weight >= 255) weight = 255;
                            ctx.fillStyle = `rgb(${255-weight}, ${weight}, ${255-weight})`;
                            ctx.fillRect(i*cellWidth, j*cellHeight, cellWidth, cellHeight); 
                        }
                    }
                }

                function update(weights) {
                    window.requestAnimationFrame(() => {render(weights);});
                }
                
                $("canvas").click(() => {
                    $.ajax({
                        url: 'perceptron.php',
                        data: {
                            epochs: $("#epochs").val(),
                            samples: $("#samples").val()
                        },
                        type: 'GET',
                        dataType: 'json',
                        success: (data) => {
                            trainAccuracy = data[0];
                            testAccuracy = data[1];
                            let weights = data[2];
                            update(weights);
                            $("#train").replaceWith(`<p id="train">Train Accuracy: ${trainAccuracy}</p>`);
                            $("#test").replaceWith(`<p id="test">Test Accuracy: ${testAccuracy}</p>`);
                        }
                    });
                });
                $("#before").before(`<p id="train">Train Accuracy: ${trainAccuracy}</p>\n<p id="test">Test Accuracy: ${testAccuracy}</p>`);
            });
        </script>
        <p id="before">Epochs:</p><input type="range" id="epochs" name="epochs" min="1" max="30"></input>
        <p>Samples:</p><input type="range" id="samples" name="samples" min="100" max="800"></input>
    </body>
</html> 