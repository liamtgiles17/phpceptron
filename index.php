<!DOCTYPE html>
<html>
    <head>
        <title>perceptron.php</title>
    </head>
    <style></style>
    <body>
        <h1>PHP Perceptron Demo</h1>
        <canvas id="weights" width="512" height="512"></canvas>
        <?php
        const WIDTH = 30;
        const HEIGHT = 30;
        const BIAS = -1;

        const SHAPE_PADDING = 5;
        const SHAPE_MAX_SIZE = 20;

        function initialise_weights(): array {
            $weights = [];
            for ($i = 0; $i < HEIGHT; $i++) {
                $weights[] = [];
                for ($j = 0; $j < WIDTH; $j++) {
                    $weights[$i][] = 0;
                }
            }
            return $weights;
        }

        function draw_rectangle(): array {
            $rectangle = [];
            $x = rand(0, WIDTH-SHAPE_PADDING);
            $y = rand(0, HEIGHT-SHAPE_PADDING);
            $w = rand(SHAPE_PADDING, SHAPE_PADDING+SHAPE_MAX_SIZE);
            $h = rand(SHAPE_PADDING, SHAPE_PADDING+SHAPE_MAX_SIZE);

            for ($i = 0; $i < HEIGHT; $i++) {
                $rectangle[] = [];
                for ($j = 0; $j < WIDTH; $j++) {
                    if ($j >= $x && $j <= ($x+$w) && $i >= $y && $i <= ($y+$h)) {
                        $rectangle[$i][] = 1;
                    } else {
                        $rectangle[$i][] = 0;
                    }
                }
            }
            return $rectangle;
        }

        function draw_circle(): array {
            $circle = [];
            $x = rand(SHAPE_PADDING, WIDTH-SHAPE_PADDING);
            $y = rand(SHAPE_PADDING, HEIGHT-SHAPE_PADDING);
            $r = rand(SHAPE_PADDING, SHAPE_PADDING+SHAPE_MAX_SIZE);
            
            for ($i = 0; $i < HEIGHT; $i++) {
                $circle[] = [];
                for ($j = 0; $j < WIDTH; $j++) {
                    if (($j-$x)**2 + ($i-$y)**2 <= $r**2) {
                        $circle[$i][] = 1;
                    } else {
                        $circle[$i][] = 0;
                    }
                }
            }
            return $circle;
        }

        function visualise($shape): void {
            for ($i = 0; $i < HEIGHT; $i++) {
                echo implode(" ", $shape[$i]);
                echo "\n";
            }
        }

        function generate_dataset($n): array {
            $images = [];
            $labels = [];
            for ($i = 0; $i < $n; $i++) {
                $choice = rand(0, 1);
                if ($choice === 0) {
                    $images[] = draw_rectangle();
                    $labels[] = 0;
                } else {
                    $images[] = draw_circle();
                    $labels[] = 1;
                }
            }
            return [$images, $labels];
        }

        function feed_forward($input, &$weights): int {
            $output = 0;
            $z = 0.0;

            for ($i = 0; $i < HEIGHT; $i++) {
                for ($j = 0; $j < WIDTH; $j++) {
                    $z += $weights[$j][$i]*$input[$j][$i];
                }
            }
            if ($z >= BIAS) $output+=1;

            return $output;
        }

        function train(&$inputs, &$labels, &$weights, $epochs): float {
            $dataset_length = count($inputs);
            $n_correct = 0;
            for ($j = 0; $j < $epochs; $j++) {
                for ($i = 0; $i < $dataset_length; $i++) {
                    $output = feed_forward($inputs[$i], $weights);
                    if ($output === 0) {
                        if ($labels[$i] === 1) {
                            for ($y = 0; $y < HEIGHT; $y++) {
                                for ($x = 0; $x < WIDTH; $x++) {
                                    if ($inputs[$i][$y][$x] === 1) {
                                        $weights[$y][$x] += 1;
                                    }
                                }
                            }
                        } else {
                            $n_correct++;
                        }
                    } else if ($output === 1) {
                        if ($labels[$i] === 0) {
                            for ($y = 0; $y < HEIGHT; $y++) {
                                for ($x = 0; $x < WIDTH; $x++) {
                                    if ($inputs[$i][$y][$x] === 1) {
                                        $weights[$y][$x] -= 1;
                                    }
                                }
                            }
                        } else {
                            $n_correct++;
                        }
                    }
                }
            }
            return $n_correct/($dataset_length*$epochs);
        }

        function test(&$inputs, &$labels, &$weights): float {
            $dataset_length = count($inputs);
            $n_correct = 0;
            for ($i = 0; $i < $dataset_length; $i++) {
                $output = feed_forward($inputs[$i], $weights);
                if ($output === $labels[$i]) {
                    $n_correct++;
                }
            }
            return $n_correct/$dataset_length;
        }

        function array_to_string($array): string {
            $result = "[".implode(", ", $array)."]";
            return $result;
        }

        function vector_to_string($vector): string {
            $result = "[\n";
            for ($i = 0; $i < count($vector); $i ++) {
                $result .= array_to_string($vector[$i]).",\n";
            }
            $result .= "]";
            return $result;
        }

        $train_dataset = generate_dataset(500);
        $train_inputs = $train_dataset[0];
        $train_labels = $train_dataset[1];
        $test_dataset = generate_dataset(150);
        $test_inputs = $test_dataset[0];
        $test_labels = $test_dataset[1];
        $weights = initialise_weights();
        $train_accuracy = train($train_inputs, $train_labels, $weights, 20);
        $test_accuracy = test($test_inputs, $test_labels, $weights);

        echo '<script>';
        echo 'const WIDTH = '.WIDTH.';'."\n";
        echo 'const HEIGHT = '.HEIGHT.';'."\n";
        echo 'const canvas = document.getElementById("weights");'."\n";
        echo 'const ctx = canvas.getContext("2d");'."\n";
        echo 'let cellWidth = Math.floor(ctx.canvas.width/WIDTH);'."\n";
        echo 'let cellHeight = Math.floor(ctx.canvas.height/HEIGHT);'."\n";
        echo 'var weights = '.vector_to_string($weights).';'."\n";
        echo 'function render() {'."\n";
        echo '    ctx.clearRect(0, 0, 512, 512);'."\n";
        echo '    for (let i = 0; i < HEIGHT; i++) {'."\n";
        echo '        for (let j = 0; j < WIDTH; j++) {'."\n";
        echo '            let weight = weights[j][i];'."\n";
        echo '            weight += 127;'."\n";
        echo '            if (weight <= 0) weight = 0;'."\n";
        echo '            if (weight >= 255) weight = 255;'."\n";
        echo '            ctx.fillStyle = `rgb(127, ${weight}, 255)`;'."\n";
        echo '            ctx.fillRect(cellWidth*i, cellHeight*j, cellWidth, cellHeight);'."\n";
        echo '        }'."\n";
        echo '    }'."\n";
        echo '    window.requestAnimationFrame(render);'."\n";
        echo '}'."\n";
        echo 'render();'."\n";
        echo '</script>';
        echo '<p>Train Accuracy: '.$train_accuracy.'</p>';
        echo '<p>Test Accuracy: '.$test_accuracy.'</p>';
        ?>
    </body>
</html>