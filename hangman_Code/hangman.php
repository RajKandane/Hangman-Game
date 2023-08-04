<?php
session_start();

// Function to choose a word randomly from the words file
function getRandomWord()
{
    $wordsFile = 'words.txt';
    $words = file($wordsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return strtoupper($words[array_rand($words)]);
}

// Function to initialize a new game
function initializeGame()
{
    $_SESSION['word'] = getRandomWord();
    $_SESSION['guessedLetters'] = [];
    $_SESSION['wrongGuesses'] = 0;
}

// Function to process the player's guess
function processGuess($letter)
{
    $letter = strtoupper($letter);
    $word = $_SESSION['word'];

    if (!in_array($letter, $_SESSION['guessedLetters'])) {
        $_SESSION['guessedLetters'][] = $letter;

        if (strpos($word, $letter) === false) {
            $_SESSION['wrongGuesses']++;
        }
    }
}

// Function to use the hint
function useHint()
{
    $word = $_SESSION['word'];
    $guessedLetters = $_SESSION['guessedLetters'];

    if (count($guessedLetters) < strlen($word)) {
        $unrevealedLetters = array_diff(str_split($word), $guessedLetters);
        $lettersToReveal = ceil(count($unrevealedLetters) / 2);

        while ($lettersToReveal > 0 && count($unrevealedLetters) > 0) {
            $hintPosition = array_rand($unrevealedLetters);
            $_SESSION['guessedLetters'][] = $unrevealedLetters[$hintPosition];
            unset($unrevealedLetters[$hintPosition]);
            $lettersToReveal--;
        }
    }
}

// Function to check if the game is complete (won or lost)
function isGameComplete()
{
    $word = $_SESSION['word'];
    $guessedLetters = $_SESSION['guessedLetters'];

    // Check if all letters in the word are guessed
    if (count(array_diff(array_unique(str_split($word)), $guessedLetters)) === 0) {
        return 'win';
    }

    // Check if the player has made too many wrong guesses
    if ($_SESSION['wrongGuesses'] >= 6) {
        return 'lose';
    }

    return false;
}

// Start a new game if not already started or restart the game on button click
if (empty($_SESSION['word']) || isset($_GET['restart'])) {
    initializeGame();
}

// Process the player's guess if a letter is submitted
if (isset($_GET['thelet']) && ctype_alpha($_GET['thelet'])) {
    processGuess($_GET['thelet']);
}

// Use the hint if the "Hint" button is clicked
if (isset($_GET['hint'])) {
    useHint();
}

// Check if the game is complete
$gameStatus = isGameComplete();
if ($gameStatus === 'win' || $gameStatus === 'lose') {
    // Game over, start a new game
    $wordToReveal = $_SESSION['word'];
    initializeGame();

    // If the game is lost, set the wrongGuesses to 6 for the last image
    if ($gameStatus === 'lose') {
        $_SESSION['wrongGuesses'] = 6;
    }
}
?>

<div class="hangman-container">
    <h1>Hangman Game</h1>

    <!-- Display the gallows image -->
    <img src="images/hangman_<?php echo $_SESSION['wrongGuesses']; ?>.png" alt="Gallows Image">

    <!-- Display the word with blanks and guessed letters -->
    <p>
        <?php
        $word = $_SESSION['word'];
        $guessedLetters = $_SESSION['guessedLetters'];

        // Loop through each letter in the word
        for ($i = 0; $i < strlen($word); $i++) {
            $letter = $word[$i];

            // If the letter is guessed, show it, otherwise show a blank
            if (in_array($letter, $guessedLetters)) {
                echo $letter . ' ';
            } else {
                echo '_ ';
            }
        }
        ?>
    </p>

    <!-- Display the letters for the player to guess -->
    <div>
        <?php
        for ($i = 0; $i < 26; $i++) {
            $letter = chr(65 + $i);
            // Check if the letter is already guessed
            if (in_array($letter, $guessedLetters)) {
                echo "<span>$letter</span>";
            } else {
                echo "<a href=\"?thelet=$letter\">$letter</a>";
            }

            // Add line break for every 7 letters
            if (($i + 1) % 7 === 0) {
                echo "<br>";
            }
        }
        ?>
    </div>

    <!-- Display the "Hint" button -->
    <div>
        <a href="?hint">Hint</a>
    </div>

    <!-- Display the "Restart Game" button -->
    <div>
        <form method="get">
            <button type="submit" name="restart">Restart Game</button>
        </form>
    </div>

    <!-- Display the number of chances remaining -->
    <div class="chances">
        <?php echo "Chances Remaining: " . (6 - $_SESSION['wrongGuesses']); ?>
    </div>

    <!-- Display win or lose message if the game is complete -->
    <?php if ($gameStatus === 'win'): ?>
        <p class="win-message">Congratulations! You Won!</p>
    <?php elseif ($gameStatus === 'lose'): ?>
        <p class="lose-message">Sorry! You Lost!</p>
        <p class="word-reveal">The word was: <?php echo $wordToReveal; ?></p>
    <?php endif; ?>
</div>
