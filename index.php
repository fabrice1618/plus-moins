<?php
require_once("config.php");
require_once("vue_prenom.php");
require_once("vue_jeu.php");

session_start();

$vue = "non_definie";

// Vérification si la session doit être réinitialisée
if (isset($_GET['reset'])) {
    session_unset();
    $vue = "vue_prenom";
//    header("Location: game.php");
//    exit();
}


try {
    // Connexion à la base de données avec PDO
    $dsn = "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DB;
    $conn = new PDO($dsn, MYSQL_USER, MYSQL_PWD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification si le prénom existe dans la session
    if (isset($_SESSION['prenom'])) {
        $prenom = $_SESSION['prenom'];
    } else {
        // Affichage du formulaire pour demander le prénom
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' &&
            isset($_POST['prenom'])
            ) {
            $prenom = $_POST['prenom'];
            $_SESSION['prenom'] = $prenom;
        } else {

            // Récupération de la liste des joueurs classés par moyenne du nombre de tentatives
            $sql = "SELECT prenom, AVG(tentatives) AS moyenne_tentatives FROM parties GROUP BY prenom ORDER BY moyenne_tentatives";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo vue_prenom();

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

if ($vue == "vue_jeu") {
    echo vue_jeu();
} elseif ($vue == "vue_prenom") {
    echo "Vue prénom ?";
} else {
    die("Erreur: vue $vue inconnue");
}

// Fermeture de la connexion à la base de données
$conn = null;
?>
