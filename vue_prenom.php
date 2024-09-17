<?php 

function vue_prenom()
{
    global $result;

    $liste_joueurs = "";

    foreach ($result as $row) {
        $liste_joueurs .= '<li>' . $row['prenom'] . ' - Moyenne : ' . $row['moyenne_tentatives'] . '</li>';
    }

    $codeHtml = <<<"END"
    <!DOCTYPE html>
    <html>
    <head>
        <title>Mini jeu PHP</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <h1>Mini jeu PHP</h1>
            <form method="POST" action="index.php">
                <label for="prenom">Entrez votre prénom :</label>
                <input type="text" id="prenom" name="prenom" required>
                <button type="submit">Commencer</button>
            </form>
            <h3>Liste des joueurs :</h3>
            <ul>
            $liste_joueurs
            </ul>
        </div>
    </body>
    </html>
    END;

    return $codeHtml;
}