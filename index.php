<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <form method="GET">
        <input type="text" name="pokemonId" placeholder="Input an ID or name">
        <button type="submit">Search</button>
    </form>
</header>

<?php
function fetchData($pokemonId) {
    $data = file_get_contents('https://pokeapi.co/api/v2/pokemon/' . $pokemonId);
    return json_decode($data, true);
}

// If user has not submitted the form pokemonId will not be set
// If user clicked on submit button without filling a value pokemonId will be empty string ''
if (isset($_GET['pokemonId']) && $_GET['pokemonId'] !== '') {
    // Get pokemon necessary data
    $inputVal = $_GET['pokemonId'];
    $data = fetchData($inputVal);
    $id = $data['id'];
    $name = $data['name'];

    // Check for the moves array length
    // Ditto has only 1 move
    // Or handle pokemon with 2 or 3 moves only
    $count = count($data['moves']);
    $isDitto = false;
    if ($count > 3) {
        $max = 4;
    }else if ($count === 1) {
        $max = $count;
        $isDitto = true;
    }else {
        $max = $count;
    }

    if ($isDitto) {
        // If its Ditto get the first move name
        $moves = [$data['moves'][0]['move']['name']];
    }else {
        // If not Ditto get 4 random moves or less
        $randomArrayIndexes = array_rand($data['moves'], $max);
        // array_map loops through each array item and uses the current array item as a function parameter
        // and makes a new array containing the returned values of the function
        $moves = array_map(function($index) {
            global $data; // global keyword allows us to access global variables that are initialize outside of the function
            return $data['moves'][$index]['move']['name'];
        }, $randomArrayIndexes);
    }

    $imgUrl = $data['sprites']['front_default'];
    $speciesUrl = $data['species']['url'];
    $speciesDataArr = json_decode(file_get_contents($speciesUrl), true);

    $hasPrevEvolution = false;
    if ($speciesDataArr['evolves_from_species'] !== null) {
        $hasPrevEvolution = true;
        $evolvesFromName = $speciesDataArr['evolves_from_species']['name'];
        // Fetch previous evolution pokemon data
        $evolvesFromDetailsArr = json_decode(file_get_contents('https://pokeapi.co/api/v2/pokemon/' . $evolvesFromName), true);
        $evolvesFromImgUrl = $evolvesFromDetailsArr['sprites']['front_default'];
    }
?>

    <div id="main">
        <div class="details">
            <h2 class="name"><?php echo $name ?></h2>
            <p class="id">ID: <?php echo $id ?></p>
            <img src="<?php $imgUrl ?>" alt="<?php $name ?>">

            <h4><?php echo $name ?>'s moves are:</h4>
            <?php

            foreach ($moves as $move) {
            ?>
                <p class="single-move"><?php echo $move ?></p>
            <?php
            }
            ?>
        </div>

        <?php
        if ($hasPrevEvolution) {
            ?>
            <div class="prev-evolution">
                <h2>Evolves from:</h2>
                <img src="<?php echo $evolvesFromImgUrl ?>" alt="<?php echo $evolvesFromName ?>">
                <p class="prev-evolution-name"><?php echo $evolvesFromName ?></p>
            </div>
            <?php
        }
        ?>
    </div>

<?php
}else {
    echo '<p class="alert-danger">Please input a valid ID or name</p>';
} // END OF IF isset($_GET['pokemonId'])
?>
</body>
</html>