import random
import time
import json

achievements = []

difficulty = 1
difficulty_multiplier = 1.0

save_data = {
    'score': 0,
    'reset': 3,
    'level': 1,
    'highest_level': 1,
    'time_limit': 10,
}

score = 0
reset = 3
level = 1
highest_level = 1
time_limit = 10

def show_achievements():
    if len(achievements) > 0:
        print("=== Achievements ===")
        for achievement in achievements:
            print(achievement)
    else:
        print("Tidak ada achievement yang tercapai.")

def get_difficulty(level):
    return 1 + (level // 5)

def get_time_limit(difficulty):
    return 10 - (difficulty - 1) * 2

def show_special_event(level):
    if level == 10:
        print("Special Event: Anda mencapai Level 10! Selamat!")

def save_game():
    global save_data
    with open('save.json', 'w') as file:
        json.dump(save_data, file)

def load_game():
    try:
        with open('save.json', 'r') as file:
            return json.load(file)
    except FileNotFoundError:
        return None

def play_game():
    global score, reset, level, highest_level, time_limit

    if load_data := load_game():
        score = load_data['score']
        reset = load_data['reset']
        level = load_data['level']
        highest_level = load_data['highest_level']
        time_limit = load_data['time_limit']

    print("Welcome to Game")
    print("Rules: Saat kamu salah menebak, skor akan dikurangkan -5. Hati-hati!\n")

    winning_score = 20

    while reset > 0:
        print(f"====== GAME LEVEL {level} ======")
        print("Tebak random (1-9)")
        computer = random.randint(1, 9)
        player = input("Masukkan angka : ")

        if not player.isnumeric() or int(player) < 1 or int(player) > 9:
            print("Masukkan angka valid antara 1 dan 9.")
            continue

        player = int(player)

        if player == computer:
            print(f"Menang! Angka yang benar adalah {computer}")
            score += winning_score * level
            reset = 3
            level += 1
            highest_level = max(highest_level, level)

            show_special_event(level)

            if level == 5 and 'Level 5 Clear' not in achievements:
                achievements.append('Level 5 Clear')

            difficulty = get_difficulty(level)
            difficulty_multiplier = difficulty * 0.2 + 1.0
            time_limit = get_time_limit(difficulty)

            save_game()  

            if level <= highest_level:
                print(f"Level tertinggi yang dicapai: {highest_level}")

        else:
            print(f"Salah! Angka yang benar adalah {computer}")
            score -= 5
            reset -= 1

        print(f"Skor Anda: {score}")

        if level % 3 == 0:
            time_limit += 5

        if reset > 0:
            print(f"Tersisa {reset} kesempatan lagi.")
            print(f"Anda memiliki waktu {time_limit} detik untuk menebak.")
            start_time = time.time()

            while True:
                player = input("Masukkan angka : ")

                if not player.isnumeric() or int(player) < 1 or int(player) > 9:
                    print("Masukkan angka valid antara 1 dan 9.")
                    continue

                player = int(player)
                elapsed_time = time.time() - start_time

                if player == computer:
                    print(f"Menang! Angka yang benar adalah {computer}")
                    score += winning_score * level
                    reset = 3
                    level += 1
                    highest_level = max(highest_level, level)
                else:
                    print(f"Salah! Angka yang benar adalah {computer}")
                    score -= 5
                    reset -= 1

                if elapsed_time > time_limit:
                    print("Waktu habis! Kesempatan menebak berkurang.")
                    reset -= 1
                elif player > computer:
                    print("Hint: Angka yang benar lebih kecil.")
                elif player < computer:
                    print("Hint: Angka yang benar lebih besar.")

                print(f"Skor Anda: {score}")

                if level % 3 == 0:
                    time_limit += 5

                if level <= highest_level:
                    print(f"Level tertinggi yang dicapai: {highest_level}")

                if reset == 0:
                    break

            save_game()

    print(f"Permainan berakhir. Skor akhir Anda: {score}")

    show_achievements()

    play_again = input("Apakah Anda ingin bermain lagi? (yes/no) : ").lower()

    if play_again == 'yes':
        play_game()
    else:
        print("Terima kasih telah bermain!")

play_game()
