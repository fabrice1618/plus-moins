<?php
session_start();

// Informations de connexion à la base de données (à personnaliser avec vos informations)
$servername = "localhost";
$username = "votre_nom_utilisateur";
$password = "votre_mot_de_passe";
$dbname = "nom_de_la_base_de_donnees";

try {
    // Connexion à la base de données avec PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification si la session doit être réinitialisée
    if (isset($_GET['reset'])) {
        session_destroy();
        header("Location: game.php");
        exit();
    }

    // Vérification si le prénom existe dans la session
    if (isset($_SESSION['prenom'])) {
        $prenom = $_SESSION['prenom'];
    } else {
        // Affichage du formulaire pour demander le prénom
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prenom'])) {
            $prenom = $_POST['prenom'];
            $_SESSION['prenom'] = $prenom;
        } else {
            // Affichage du formulaire de saisie du prénom et liste des joueurs
            echo '<form method="POST" action="">';
            echo '<label for="prenom">Entrez votre prénom :</label>';
            echo '<input type="text" id="prenom" name="prenom" required>';
            echo '<button type="submit">Commencer</button>';
            echo '</form>';

            // Récupération de la liste des joueurs classés par moyenne du nombre de tentatives
            $sql = "SELECT prenom, AVG(tentatives) AS moyenne_tentatives FROM parties GROUP BY prenom ORDER BY moyenne_tentatives";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo '<h3>Liste des joueurs :</h3>';
            echo '<ul>';
            foreach ($result as $row) {
                echo '<li>' . $row['prenom'] . ' - Moyenne : ' . $row['moyenne_tentatives'] . '</li>';
            }
            echo '</ul>';

            exit();
        }
    }

    // Récupération des statistiques du joueur depuis la base de données
    $sql = "SELECT COUNT(*) AS nb_parties, SUM(tentatives) AS total_tentatives, MIN(tentatives) AS min_tentatives, MAX(tentatives) AS max_tentatives FROM parties WHERE prenom = :prenom";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':prenom', $prenom);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $nbParties = $row['nb_parties'];
    $totalTentatives = $row['total_tentatives'];
    $moyenneTentatives = $nbParties > 0 ? $totalTentatives / $nbParties : 0;
    $minTentatives = $row['min_tentatives'];
    $maxTentatives = $row['max_tentatives'];

    // Vérification si le joueur a soumis une tentative
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tentative'])) {
        $tentative = $_POST['tentative'];
        $nombreTire = $_SESSION['nombreTire'];

        // Vérification de la tentative du joueur
        if ($tentative > $nombreTire) {
            $message = 'Moins';
        } elseif ($tentative < $nombreTire) {
            $message = 'Plus';
        } else {
            $message = 'Félicitations, vous avez trouvé le nombre !';

            // Enregistrement du résultat de la partie dans la base de données
            $nbTentatives = $_SESSION['nbTentatives'];
            $sql = "INSERT INTO parties (prenom, tentatives) VALUES (:prenom, :tentatives)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':tentatives', $nbTentatives);
            $stmt->execute();

            // Réinitialisation de la session pour une nouvelle partie
            unset($_SESSION['nombreTire']);
            unset($_SESSION['nbTentatives']);
        }
    } elseif (!isset($_SESSION['nombreTire'])) {
        // Commencer une nouvelle partie
        $_SESSION['nombreTire'] = rand(1, 9);
        $_SESSION['nbTentatives'] = 0;
        $message = 'Une nouvelle partie commence !';
    }
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Mini jeu PHP</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Mini jeu PHP</h1>
        <p>Bienvenue, <?php echo $prenom; ?> !</p>
        <p>Nombre de parties jouées : <?php echo $nbParties; ?></p>
        <p>Nombre de tentatives totales : <?php echo $totalTentatives; ?></p>
        <p>Moyenne du nombre de tentatives pour réussir : <?php echo $moyenneTentatives; ?></p>
        <p>Partie la plus courte : <?php echo $minTentatives; ?> tentatives</p>
        <p>Partie avec le plus de tentatives : <?php echo $maxTentatives; ?> tentatives</p>
        <hr>
        <h3><?php echo $message; ?></h3>
        <?php if (!isset($_SESSION['nombreTire'])) : ?>
            <form method="POST" action="">
                <input type="number" name="tentative" required>
                <button type="submit">Tenter</button>
            </form>
        <?php else : ?>
            <button onclick="window.location.reload();">Commencer une partie</button>
        <?php endif; ?>
        <a href="game.php?reset=true">Réinitialiser la session</a>
    </div>
</body>
</html>

<?php
// Fermeture de la connexion à la base de données
$conn = null;
?>
