<?php
// Constantes qui serviront au cours du script.
// -------------------------------------------
$CONSTANTES['adresseIp'] = "80.240.136.144";
$CONSTANTES['port'] = "389";
$CONSTANTES['uid'] = (int) file_get_contents("uid.txt"); // Ici on r�cup�re l'uid incr�ment� par rapport au dernier utilisateur ajout�.
// -------------------------------------------

if (!isset($_POST['nom']))
{
// Si on n'a pas re�u de donn�es, on affiche le formulaire
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="windows-1252">
    <title>Ajouter un nouvel utilisateur au serveur LDAP <?php echo '[<span class="red">'.$CONSTANTES['adresseIp'].':'.$CONSTANTES['port'].'</span>]'; ?></title> <!-- On r�cup�re simplement l'adresse ip du serveur et le port sur lequel on se connecte pour savoir de quoi on parle. -->
    <meta name="description" content="Ajouter un nouvel utilisateur au serveur LDAP">
    <link rel="stylesheet" href="classic.css"> 
  </head>
  <body>
  <h1>Ajouter un nouvel utilisateur au serveur LDAP <?php echo '[<span class="red">'.$CONSTANTES['adresseIp'].':'.$CONSTANTES['port'].'</span>]'; ?></h1>
  
  <!-- D�but du formulaire -->
  
	<form method="post" action="">

	<fieldset>
    <legend>Ajouter un utilisateur :</legend>

       <label for="nom">Votre nom :</label>

       <input type="text" name="nom" id="nom" />
	   <br />
	   (Utiliser uniquement les lettres de l'alphabets (sans accents) et le trait d'union '-', touche 6 du clavier )
	   <br />
	   
	   <label for="prenom">Votre prenom :</label>

       <input type="text" name="prenom" id="prenom" />
	   <br />
		(Utiliser uniquement les lettres de l'alphabets (sans accents) et le trait d'union '-', touche 6 du clavier )
       <br />

       <label for="mdp">Votre mot de passe :</label>

       <input type="password" name="mdp" id="mdp" />
	   
	   <br />
	   <br />
	   <label for="confmdp">Confirmation de votre mot de passe :</label>

       <input type="password" name="confmdp" id="confmdp" />
		
	
       

    </fieldset>
	
	<fieldset>
    <legend>Options avanc�es :</legend>
	
       <label for="ip">Modifier l'adresse du serveur distant :</label>

       <input type="text" name="ip" id="ip" />
	   <br />
	   (de la forme x.x.x.x o� x se situe entre 0 et 255) 
	   <br />
	   
	   <label for="port">Modifier le port de connection du serveur distant :</label>

       <input type="text" name="port" id="port" />
	   <br />
	   (Ins�rer un num�ro entre 1 et 65535 inclus)
	   <br />
	   
	   <label for="gid">Modifier l'identifiant du groupe de l'utilisateur � ajouter :</label>

       <input type="text" name="gid" id="gid" />
	   <br />
	   (<span class="red">Attention ! Le script garde la valeur '500' par d�faut.</span>)
	   
	   <br />
    
	   <label for="uid">Modifier l'identifiant de l'utilisateur � ajouter :</label>

       <input type="text" name="uid" id="uid" />
	   <br />
	   (<span class="red"">Attention ! Le script autoincr�mente d�j� la valeur de l'identifiant de l'utilisateur pr�c�demment ajout�.</span>)
	   
	   
	   </fieldset>
		<input type="submit" value="Envoyer" />
	</form>
  </body>
</html>

<?php
}
else
{
// Sinon si on a re�u des donn�es (uniquement celles qui sont n�cessaires)...
	if(!empty($_POST['nom']) AND !empty($_POST['prenom']) AND !empty($_POST['mdp']) AND !empty($_POST['confmdp']))
	{
		// ...on v�rifie le genre des donn�es nom et pr�nom
		if(preg_match("#^[a-zA-Z-]+$#",$_POST['nom']) AND preg_match("#^[a-zA-Z-]+$#",$_POST['prenom']))
		{

			// On traite les donn�es : nom et pr�nom
			$nom = ucfirst(strtolower($_POST['nom']));
			$prenom = ucfirst(strtolower($_POST['prenom']));
			$lettre = array();
			if(preg_match("#-([a-z])#",$nom,$lettre))
			{
				$lettre = strtoupper($lettre[1]);
				$nom = preg_replace("#-[a-z]#","-$lettre",$nom); 
			}
			$lettre = array();
			if(preg_match("#-([a-z])#",$prenom,$lettre))
			{
				$lettre = strtoupper($lettre[1]);
				$prenom = preg_replace("#-[a-z]#","-$lettre",$prenom); 
			}
			
			$mdp = sha1($_POST['mdp']);
			$confmdp = sha1($_POST['confmdp']);
			
			// Si les mots de passe concordent on regarde si des options suppl�mentaires ont �t� utilis�es
			if($mdp == $confmdp )
			{
				// On initialise le gid � 500 par d�faut ou la valeur optionnelement transmise via le formulaire
				if(!empty($_POST['gid']) AND preg_match("#^[0-9]{1,}$#",$_POST['gid']))
				$gid = $_POST['gid'];
				else $gid = 500;
				
				// Si une nouvelle valeur pour l'uid a �t� transmise, on met � jour le nombre et on incr�mentera � partir de celui-l� dor�navant
				if(!empty($_POST['uid']) AND preg_match("#^[0-9]{1,}$#",$_POST['uid']))
				{
					$uid = $_POST['uid'];
					$monfichier2 = fopen('uid.txt', 'w+');
					fseek($monfichier2, 0); // On remet le curseur au d�but du fichier
					fputs($monfichier2, $uid); // On �crit le nouveau uid
					fclose($monfichier2);
				}
				else $uid = $CONSTANTES['uid'];
				
				// On v�rifie que l'adresse ip en est bien une
				if(!empty($_POST['ip']))
				{
					if(preg_match("#^([0-9]{3})\\.([0-9]{3})\\.([0-9]{3})\\.([0-9]{3})$#",$_POST['ip'],$ip) AND $ip[1] >= 0 AND $ip[1]<256 AND $ip[2] >= 0 AND $ip[2]<256 AND $ip[3] >= 0 AND $ip[3]<256 AND $ip[4] >= 0 AND $ip[4]<256)
					{
						$ip = $_POST['ip'];
					}
				}
				else $ip = $CONSTANTES['adresseIp'];
				
				// On v�rifie que le port en est bien un
				if(!empty($_POST['port']))
				{
					if($_POST['port'] > 0 AND $_POST['port'] < 65536 AND preg_match("#^[0-9]{1,5}$#",$_POST['port']))
					{
						$port = $_POST['port'];
					}
				}
				else $port = $CONSTANTES['port'];
				
				// On a maintenant toutes nos variables saines et on va cr�er les derni�res constantes n�cessaires au profil :
				
				$nomtmp = preg_replace("#-#","",$nom);
				$diminutif = strtolower(substr($prenom,0,1)).strtolower($nomtmp);
				
				$ds = ldap_connect($ip, $port);  // on se connecte au serveur LDAP distant
				ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
				if ($ds)
				{

					echo 'test de connection : succ�s';
					$r = ldap_bind($ds, "cn=admin,dc=rBOX,dc=lan", "root");
					echo '<br />liaison avec le serveur effectu�';

					// On pr�pare les donn�es du profil � ajouter

					$info["cn"] = "$diminutif";
					$info["gidNumber"] = "$gid";
					$info["gn"] = "$prenom";
					$info["homeDirectory"] = "/home/users/$diminutif";
					$info["objectClass"][0] = "inetOrgPerson";
					$info["objectClass"][1] = "posixAccount";
					$info["objectClass"][2] = "top";
					$info["sn"] = "$prenom";
					$info["userPassword"] = $_POST['mdp'];
					$info["userid"] = "$diminutif";
					$info["uidNumber"] = "$uid";

					// On ajoute les donn�es au dossier
					$r = ldap_add($ds, "cn=$diminutif,ou=users,dc=rBOX,dc=lan", $info);
					
					echo '<br />Les donn�es ont �t� correctement ajout�es !';
					
					ldap_close($ds);
					
					// On incr�mente automatiquement l'uid 
					$mf = (int) file_get_contents("uid.txt");
					$mf++;
					$monfichier2 = fopen('uid.txt', 'w+');
					fseek($monfichier2, 0); // On remet le curseur au d�but du fichier
					fputs($monfichier2, $mf); // On �crit le nouveau uid
					fclose($monfichier2);

				}
				else
				{
					echo "Impossible de se connecter au serveur LDAP";
				}
			}
		}
	}
}
?>