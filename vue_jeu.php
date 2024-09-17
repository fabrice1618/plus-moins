<?php 

function vue_jeu()
{
    global $prenom;
    global $message;
    global $nbParties;
    global $totalTentatives;
    global $moyenneTentatives;
    global $minTentatives;
    global $maxTentatives;

    if (!isset($_SESSION['nombreTire'])) 
    {
        $html2 = <<<"FOOBAR"
        <form method="POST" action="">
            <input type="number" name="tentative" required>
            <button type="submit">Tenter</button>
        </form>
        FOOBAR;    
    } else {
        $html2 = '<button onclick="window.location.reload();">Commencer une partie</button>';
    }


    $codeHtml = <<<"FOOBAR"
    <!DOCTYPE html>
    <html>
    <head>
        <title>Mini jeu PHP</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <h1>Mini jeu PHP</h1>
            <p>Bienvenue, $prenom !</p>
            <p>Nombre de parties jouées : $nbParties</p>
            <p>Nombre de tentatives totales : $totalTentatives</p>
            <p>Moyenne du nombre de tentatives pour réussir : $moyenneTentatives</p>
            <p>Partie la plus courte : $minTentatives tentatives</p>
            <p>Partie avec le plus de tentatives : $maxTentatives tentatives</p>
            <hr>
            <h3>$message</h3>
            $html2
            <a href="index.php?reset=true">Réinitialiser la session</a>
        </div>
    </body>
    </html>
    FOOBAR;

    return $codeHtml;
}