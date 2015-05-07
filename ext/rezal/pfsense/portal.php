<?php
    $target = 'https://physbook.fr'; //'http://localhost/pjm-intranet/web/app_dev.php';
    $action = (isset($_POST['auth_user'])) ? '' : $target.'/boquette/rezal/internet/connexion';
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>R&amp;z@l | Chargement</title>
    </head>
    <body>
        <style>
            body {
                background-color: #B63938;
                color: white;
            }

            #wrapper {
                height:100vh;
                line-height:100vh;
                text-align:center;
            }

            #content {
                vertical-align:middle;
                display:inline-block;
                line-height:normal;
            }

            input[type="submit"] {
                font-size: 2em;
            }

            .hidden {
                display: none;
            }
        </style>
        <script type="text/javascript">
            document.write('<style>.noscript { display:none }</style>');
        </script>

        <div id="wrapper">
            <div id="content">
                <script type="text/javascript">document.write('<p>Chargement...</p>');</script>
                <form action="<?php echo $action ?>" method="post" id="redirectForm" class="noscript">
                    <input name="auth_user" class="hidden" type="text" value="<?php echo $_POST['auth_user'] ?>">
                    <input name="auth_pass" class="hidden" type="password" value="<?php echo $_POST['auth_pass'] ?>">
                    <input name="action" type="hidden" value="$PORTAL_ACTION$">
                    <input name="redirurl" type="hidden" value="$PORTAL_REDIRURL$">
                    <input name="zone" type="hidden" value="$PORTAL_ZONE$">
                        <input name="accept" type="submit" value="Connexion" />
                </form>
            </div>
            <span></span>
        </div>

        <script type="text/javascript">
            window.addEventListener('load', function() {
                document.getElementById("redirectForm").submit();
            });
        </script>
    </body>
</html>
