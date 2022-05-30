<!DOCTYPE html>
    <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">
    <link href="<?php echo $this->make_route('public/css/bootstrap-4.6.1-dist/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css" />
    <script src="<?php echo $this->make_route('js/jquery-3.6.0.min.js'); ?>"></script>
    <script src="<?php echo $this->make_route('public/js/bootstrap-4.6.1-dist/bootstrap.min.js'); ?>"></script>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-dark">
            <a class="navbar-brand text-white" href="#">DBTulbox2</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link text-white" href="<?php echo $this->make_route(); ?>">Accueil <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-expanded="false">
                    Structures DB2 
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <a class="dropdown-item" href="<?php echo $this->make_route('dbTablesExtract'); ?>">Tables, Fichiers et Vues</a>
                    <a class="dropdown-item" href="<?php echo $this->make_route('dbColumnSearch'); ?>">Recherche sur colonnes</a>
                    <a class="dropdown-item" href="<?php echo $this->make_route('dbRoutinesExtract'); ?>">Procédures stockées et fonctions</a>
                <!--    <a class="dropdown-item" href="<XXphp echo $this->make_route('dbCompSimple'); ?>">Comparaison BD simplifiée (à finaliser)</a> -->
                <!--    <a class="dropdown-item" href="#">Comparaison BD détaillée (à finaliser)</a> -->
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-expanded="false">
                    Aide SQL
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <a class="dropdown-item" href="http://www.dpriver.com/pp/sqlformat.htm?ref=g_wangz" target="_blank">Assistant SQL</a>
                    <a class="dropdown-item" href="https://codepen.io/gregja/full/pBRxdK" target="_blank">SQL generator for lazy dev</a>
                    <a class="dropdown-item" href="https://www.ibm.com/docs/fr/db2/10.5?topic=messages-sqlstate" target="_blank">SQL States DB2</a>
                    <a class="dropdown-item" href="http://www.connectionstrings.com" target="_blank">ConnectionStrings</a>
                    <a class="dropdown-item" href="http://troels.arvin.dk/db/rdbms/" target="_blank">Comparatif BD de Troels Arvin</a>
                    <a class="dropdown-item" href="http://fadace.developpez.com/sgbdcmp/fonctions/" target="_blank">Comparatif BD de Fabien Celaia</a>
                    </div>
                </li>
            </div>
        </nav>
        <section class="container">
        <?php include($this->getContent()); ?>
        </section>
        <script>
            window.addEventListener("DOMContentLoaded", (event) => {
                console.log("DOM entièrement chargé et analysé");
                <?php echo $this->getJSContent(); ?>
            });
        </script>
    </body>
</html>