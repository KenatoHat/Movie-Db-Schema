<?php
$host = 'localhost';  // Change if you're not using XAMPP
$db   = 'movies_db';  // Database name
$user = 'root';       // Default user for XAMPP
$pass = '';           // Default password for XAMPP (empty)

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$createTableQuery = "CREATE TABLE IF NOT EXISTS movies (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    genre VARCHAR(50) NOT NULL,
    release_year INT(4) NOT NULL,
    director VARCHAR(100),
    rating DECIMAL(2,1)
)";

if ($conn->query($createTableQuery) === TRUE) {
    echo "Table 'movies' created successfully or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

function addMovie($title, $genre, $release_year, $director, $rating, $conn) {
    $sql = "INSERT INTO movies (title, genre, release_year, director, rating) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisi", $title, $genre, $release_year, $director, $rating);

    if ($stmt->execute()) {
        echo "New movie added successfully\n";
    } else {
        echo "Error: " . $stmt->error . "\n";
    }
    $stmt->close();
}

function getAllMovies($conn) {
    $sql = "SELECT * FROM movies";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "ID: " . $row["id"]. " | Title: " . $row["title"]. " | Genre: " . $row["genre"].
                 " | Release Year: " . $row["release_year"]. " | Director: " . $row["director"].
                 " | Rating: " . $row["rating"] . "\n";
        }
    } else {
        echo "No movies found.\n";
    }
}

function countMovies($conn) {
    $sql = "SELECT COUNT(*) AS total_movies FROM movies";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        echo "Total number of movies: " . $row['total_movies'] . "\n";
    } else {
        echo "Error counting movies.\n";
    }
}

function searchMoviesByGenre($genre, $conn) {
    $sql = "SELECT * FROM movies WHERE genre = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $genre);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Movies in the genre '$genre':\n";
        while($row = $result->fetch_assoc()) {
            echo "Title: " . $row["title"]. " | Release Year: " . $row["release_year"].
                 " | Director: " . $row["director"] . " | Rating: " . $row["rating"] . "\n";
        }
    } else {
        echo "No movies found in the genre '$genre'.\n";
    }
    $stmt->close();
}

function updateMovie($id, $title, $genre, $release_year, $director, $rating, $conn) {
    $sql = "UPDATE movies SET title = ?, genre = ?, release_year = ?, director = ?, rating = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisdi", $title, $genre, $release_year, $director, $rating, $id);

    if ($stmt->execute()) {
        echo "Movie updated successfully\n";
    } else {
        echo "Error updating movie: " . $stmt->error . "\n";
    }
    $stmt->close();
}

function deleteMovie($id, $conn) {
    $sql = "DELETE FROM movies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Movie deleted successfully\n";
    } else {
        echo "Error deleting movie: " . $stmt->error . "\n";
    }
    $stmt->close();
}

$movieData = [
    ['title' => 'Inception', 'genre' => 'Sci-Fi', 'release_year' => 2010, 'director' => 'Christopher Nolan', 'rating' => 8.8],
    ['title' => 'The Matrix', 'genre' => 'Action', 'release_year' => 1999, 'director' => 'The Wachowskis', 'rating' => 8.7],
    ['title' => 'Parasite', 'genre' => 'Thriller', 'release_year' => 2019, 'director' => 'Bong Joon-ho', 'rating' => 8.6],
];

foreach ($movieData as $movie) {
    addMovie($movie['title'], $movie['genre'], $movie['release_year'], $movie['director'], $movie['rating'], $conn);
}

echo "Movies in the database:\n";
getAllMovies($conn);

countMovies($conn);

echo "Searching for 'Sci-Fi' movies:\n";
searchMoviesByGenre('Sci-Fi', $conn);

updateMovie(1, 'Inception', 'Sci-Fi', 2010, 'Christopher Nolan', 9.0, $conn);

deleteMovie(2, $conn);

echo "Movies in the database after update and delete:\n";
getAllMovies($conn);

$conn->close();
?>
