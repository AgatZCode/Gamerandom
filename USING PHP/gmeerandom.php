<?php
$achievements = array();

$difficulty = 1;
$difficulty_multiplier = 1.0;

$save_data = array(
    'score' => 0,
    'reset' => 3,
    'level' => 1,
    'highest_level' => 1,
    'time_limit' => 10,
);

$score = 0;
$reset = 3;
$level = 1;
$highest_level = 1;
$time_limit = 10;

function show_achievements() {
    global $achievements;
    if (count($achievements) > 0) {
        echo "=== Achievements ===\n";
        foreach ($achievements as $achievement) {
            echo $achievement . "\n";
        }
    } else {
        echo "Tidak ada achievement yang tercapai.\n";
    }
}

function get_difficulty($level) {
    return 1 + (int)($level / 5);
}

function get_time_limit($difficulty) {
    return 10 - ($difficulty - 1) * 2;
}

function show_special_event($level) {
    if ($level == 10) {
        echo "Special Event: Anda mencapai Level 10! Selamat!\n";
    }
}

function save_game() {
    global $save_data;
    file_put_contents('save.json', json_encode($save_data));
}

function load_game() {
    if (file_exists('save.json')) {
        $data = file_get_contents('save.json');
        return json_decode($data, true);
    }
    return null;
}

function play_game() {
    global $score, $reset, $level, $highest_level, $time_limit;

    if ($load_data = load_game()) {
        $score = $load_data['score'];
        $reset = $load_data['reset'];
        $level = $load_data['level'];
        $highest_level = $load_data['highest_level'];
        $time_limit = $load_data['time_limit'];
    }

    echo "Welcome to Game\n";
    echo "Rules: Saat kamu salah menebak, skor akan dikurangkan -5. Hati-hati!\n";

    $winning_score = 20;

    while ($reset > 0) {
        echo "====== GAME LEVEL {$level} ======\n";
        echo "Tebak random (1-9)\n";
        $computer = rand(1, 9);
        echo "Masukkan angka : ";
        $player = trim(fgets(STDIN));

        if (!is_numeric($player) || $player < 1 || $player > 9) {
            echo "Masukkan angka valid antara 1 dan 9.\n";
            continue;
        }

        $player = (int)$player;

        if ($player == $computer) {
            echo "Menang! Angka yang benar adalah {$computer}\n";
            $score += $winning_score * $level;
            $reset = 3;
            $level++;
            $highest_level = max($highest_level, $level);

            show_special_event($level);

            if ($level == 5 && !in_array('Level 5 Clear', $achievements)) {
                echo "Achievement Unlocked: Level 5 Clear\n";
                $achievements[] = 'Level 5 Clear';
            }

            $difficulty = get_difficulty($level);
            $difficulty_multiplier = $difficulty * 0.2 + 1.0;
            $time_limit = get_time_limit($difficulty);

            save_game();

            if ($level <= $highest_level) {
                echo "Level tertinggi yang dicapai: {$highest_level}\n";
            }

        } else {
            echo "Salah! Angka yang benar adalah {$computer}\n";
            $score -= 5;
            $reset--;
        }

        echo "Skor Anda: {$score}\n";

        if ($level % 3 == 0) {
            $time_limit += 5;
        }

        if ($reset > 0) {
            echo "Tersisa {$reset} kesempatan lagi.\n";
            echo "Anda memiliki waktu {$time_limit} detik untuk menebak.\n";
            $start_time = time();

            while (true) {
                echo "Masukkan angka : ";
                $player = trim(fgets(STDIN));

                if (!is_numeric($player) || $player < 1 || $player > 9) {
                    echo "Masukkan angka valid antara 1 dan 9.\n";
                    continue;
                }

                $player = (int)$player;
                $elapsed_time = time() - $start_time;

                if ($player == $computer) {
                    echo "Menang! Angka yang benar adalah {$computer}\n";
                    $score += $winning_score * $level;
                    $reset = 3;
                    $level++;
                    $highest_level = max($highest_level, $level);
                } else {
                    echo "Salah! Angka yang benar adalah {$computer}\n";
                    $score -= 5;
                    $reset--;
                }

                echo "Skor Anda: {$score}\n";

                if ($level % 3 == 0) {
                    $time_limit += 5;
                }

                if ($level <= $highest_level) {
                    echo "Level tertinggi yang dicapai: {$highest_level}\n";
                }

                if ($reset == 0) {
                    break;
                }

                if ($elapsed_time > $time_limit) {
                    echo "Waktu habis! Kesempatan menebak berkurang.\n";
                    $reset--;
                } elseif ($player > $computer) {
                    echo "Hint: Angka yang benar lebih kecil.\n";
                } elseif ($player < $computer) {
                    echo "Hint: Angka yang benar lebih besar.\n";
                }
            }

            save_game();
        }
    }

    echo "Permainan berakhir. Skor akhir Anda: {$score}\n";

    show_achievements();

    echo "Apakah Anda ingin bermain lagi? (yes/no) : ";
    $play_again = strtolower(trim(fgets(STDIN)));

    if ($play_again === 'yes') {
        play_game();
    } else {
        echo "Terima kasih telah bermain!\n";
    }
}

play_game();
