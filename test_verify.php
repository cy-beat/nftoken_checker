<?php

$hash = '$2y$10$cHc.9/ZvrqvuAYAPfw/HYeZlxwN4X13YcuaDpRYp0q3rFPdBt1ii2';

$password = 'admin110814';

if (password_verify($password, $hash)) {
    echo "✅ PASSWORD MATCH";
} else {
    echo "❌ PASSWORD NOT MATCH";
}