<?php

/*
 *
 * Auteur : Reivax <bernard.xav@gmail.com>
 * Modification : 15/05/11 par SoX
 *
 * Description : Generation d'un chat
 *
 */

class Chat
{
    private $droit;
    private $id_droit;
    private $sql;
    private $id_utilisateur;

    public function __construct()
    {
        $this->sql = new SQL();
        $this->droit = $_SESSION['droit'];
        $this->id_droit = $_SESSION['id_droit'];
        $this->id_utilisateur = $_SESSION['id'];
    }

    public function afficheChat()
    {
        //si le mec est un un bar
    if (@in_array('bar', $this->droit)) {
        ?>
<div id="bloc_chat">
  <div id="texte_chat">
    <?php $this->liste_messages() ?>
  </div>
  <form id="form_chat" method="POST" action="" autocomplete="off">
    <!--<textarea id="text_chat" name="text_chat"></textarea>-->
	<input type="text" id="text_chat" name="text_chat" />
    <input id="submit_form" type="submit" value="Envoyer" />
  </form>
</div>
	<?php

    } elseif (@in_array('admin', $this->droit)) {
        ?>
<div id="liste_connectes">
    <?php
    $this->list_connectes();
        ?>
</div>
	<?php

    }
    }

    public function liste_messages()
    {
        //si le mec est un un bar
    if (@in_array('bar', $this->droit)) {
        //implode pour la requete sql
      $select_droits = 'id_droit = '.implode(' OR id_droit = ', $this->id_droit);

      //on récupère les messages avec en id_expediteur ou en id_destinataire le gars, et aussi ceux dont il a le droit
      $query = $this->sql->query('SELECT login, UNIX_TIMESTAMP(`date`) as date, message FROM chat LEFT JOIN utilisateur ON (chat.id_expediteur = utilisateur.id) WHERE id_destinataire = '.$this->id_utilisateur.' OR id_expediteur = '.$this->id_utilisateur.' OR '.$select_droits.' ORDER BY date');
        echo '<ul>';
        while ($value = mysql_fetch_assoc($query)) {
            echo '<li><strong>'.$value['login'].'</strong> (<em>'.date('H:i:s', $value['date']).'</em>) : <br />'.html_entity_decode($value['message']).'</li>';
        }
        echo '</ul>';
    }
    }

    public function liste_messages_toliste($id)
    {
        if (@in_array('admin', $this->droit)) {
            //on récupère les messages de la liste
      $query = $this->sql->query('SELECT login, UNIX_TIMESTAMP(`date`) as date, message FROM chat INNER JOIN utilisateur ON (chat.id_expediteur = utilisateur.id) WHERE id_droit = '.$id.' ORDER BY date');
            echo '<ul>';
            while ($value = mysql_fetch_assoc($query)) {
                echo '<li><strong>'.$value['login'].'</strong> (<em>'.date('H:i:s', $value['date']).'</em>) : <br />'.html_entity_decode($value['message']).'</li>';
            }
            echo '</ul>';
        }
    }

    public function liste_messages_toqqn($id)
    {
        if (@in_array('admin', $this->droit)) {
            //on récupère les messages de la liste
      $query = $this->sql->query('SELECT login, UNIX_TIMESTAMP(`date`) as date, message FROM chat INNER JOIN utilisateur ON (chat.id_expediteur = utilisateur.id) WHERE id_expediteur = '.$id.' OR id_destinataire = '.$id.' ORDER BY date');
            echo '<ul>';
            while ($value = mysql_fetch_assoc($query)) {
                echo '<li><strong>'.$value['login'].'</strong> (<em>'.date('H:i:s', $value['date']).'</em>) : <br />'.html_entity_decode($value['message']).'</li>';
            }
            echo '</ul>';
        }
    }

    public function enregistrer_message($liste = '', $id = '')
    {
        $message = addslashes($_POST['text_chat']);
        if (@in_array('bar', $this->droit)) {
            //$requete = $this->sql->select('id', 'droit', "WHERE nom='admin'");
      //$id_admin = $requete[0];
      //on speach sur le droit 0 ?
      if ($_POST['text_chat'] != '') {
          $this->sql->insert('chat', '`id_expediteur`, `id_droit`, `date`, `message`', "'".$this->id_utilisateur."', '0', NOW( ), '".$message."'");
      }
      //$this->sql->insert('chat', '`id_expediteur`, `id_droit`, `date`, `message`', "'".$this->id_utilisateur."', '".$id_admin."', NOW( ), '".$message."'");
        } elseif (@in_array('admin', $this->droit)) {
            if ($liste == 'toliste') {
                if ($_POST['text_chat'] != '') {
                    $this->sql->insert('chat', '`id_expediteur`, `id_droit`, `date`, `message`', "'".$this->id_utilisateur."', '".$id."', NOW( ), '".$message."'");
                }
            } elseif ($liste == 'toqqn') {
                if ($_POST['text_chat'] != '') {
                    $this->sql->insert('chat', '`id_expediteur`, `id_destinataire`, `date`, `message`', "'".$this->id_utilisateur."', '".$id."', NOW( ), '".$message."'");
                }
            }
        }
    }

    public function Json_id_dernier_message()
    {
        //si le mec est un un bar
    if (@in_array('bar', $this->droit)) {
        //implode pour la requete sql
      $select_droits = 'id_droit = '.implode(' OR id_droit = ', $this->id_droit);

      //on récupère les messages avec en id_expediteur ou en id_destinataire le gars, et aussi ceux dont il a le droit
      $query = $this->sql->query('SELECT id FROM chat WHERE '.$select_droits.' OR id_destinataire = '.$this->id_utilisateur.' OR id_expediteur = '.$this->id_utilisateur.' ORDER BY id DESC LIMIT 0,1');
      //$query = $this->sql->query("SELECT chat.id AS id FROM chat INNER JOIN utilisateur ON (chat.id_expediteur = utilisateur.id) WHERE ".$select_droits." OR id_destinataire = ".$this->id_utilisateur." OR id_expediteur = ".$this->id_utilisateur." ORDER BY id DESC LIMIT 0,1");
      $value = mysql_fetch_assoc($query);

        $json = array('id' => $value['id']);
        echo json_encode($json);
    }
    //si le mec est un un bar
    if (@in_array('admin', $this->droit)) {
        //on récupère les messages avec en id_expediteur ou en id_destinataire le gars, et aussi ceux dont il a le droit
      //requete qui chope le max id du message de chaque droit...
      $requete1 = $this->sql->select('MAX(chat.id) as dernier_id, chat.id_droit, droit.nom', 'chat', 'LEFT JOIN `droit` ON (chat.id_droit = droit.id) GROUP BY `id_droit`');
      //max id du message de chaque personne
      $requete2 = $this->sql->select('MAX(chat.id) as dernier_id, chat.id_expediteur, chat.id_destinataire, utilisateur.login', 'chat', 'LEFT JOIN `utilisateur` ON (chat.id_expediteur = utilisateur.id) GROUP BY `id_expediteur`');

        $retour = array();
        foreach ($requete1 as $value) {
            if (isset($value['nom'])) {
                $retour[$value['nom']] = $value['dernier_id'];
            }
        }
        foreach ($requete2 as $value) {
            if ($value['login'] != '' or $value['id_destinataire'] != 0) {
                $retour[$value['login']] = $value['dernier_id'];
            }
        }
        echo json_encode($retour);
    }
    }

    public function list_connectes()
    {
        $query1 = $this->sql->query('SELECT id, nom FROM droit WHERE liste = 1 ORDER BY nom');
        $droits = array();
        while ($table = mysql_fetch_assoc($query1)) {
            $droits[] = $table;
        }

        $query2 = $this->sql->query('SELECT utilisateur.id, login, UNIX_TIMESTAMP(`derniere_connexion`) as derniere_connexion FROM espace INNER JOIN utilisateur ON (espace.id_utilisateur = utilisateur.id) WHERE `etat`=1 ORDER BY `login`');
        $admins = array();
        while ($table = mysql_fetch_assoc($query2)) {
            $admins[] = $table;
        }

        echo '<h2>Parler à une liste :</h2>';

        foreach ($droits as $value) {
            echo '<div><a id="'.$value['nom'].'" class="choix_chat" href="chat&action=toliste&id='.$value['id'].'">'.$value['nom'].'</a></div>';
        }

        echo '<h2>Parler seulement à un EAT :</h2>';

        foreach ($admins as $value) {
            if ($value['derniere_connexion'] > (time() - 80)) {
                echo '<div id="div_de_'.$value['login'].'" class="online"><a id="'.$value['login'].'" class="choix_chat" href="chat&action=toqqn&id='.$value['id'].'">'.$value['login'].'</a></div>';
            } else {
                echo '<div id="div_de_'.$value['login'].'" class="offline"><a id="'.$value['login'].'" class="choix_chat" href="chat&action=toqqn&id='.$value['id'].'">'.$value['login'].'<span id="span_de_'.$value['login'].'"></span></a></div>';
            }
        }
    }

    public function encore_connecte()
    {
        $query1 = $this->sql->query('SELECT id, login, UNIX_TIMESTAMP(`derniere_connexion`) as derniere_connexion FROM utilisateur ORDER BY login');
        $retour = array();
        while ($value = mysql_fetch_assoc($query1)) {
            $retour[$value['login']] = $value['derniere_connexion'];
        }
        echo json_encode($retour);
    }

    public function rappel_connexion()
    {
        $this->sql->update('utilisateur', ' `derniere_connexion` = NOW( )', 'id='.$this->id_utilisateur);
    }

    public function enregistrer()
    {
        if (!is_numeric($_POST['chat_droit'])) {
            $chat_droit = 0;
        }

        $this->sql->insert('chat', 'id_expediteur, id_recepteur, date, message', $this->id_utilisateur."', '0', NOW( ), '".$message);

    //echo 'ici___'.$_POST['toto'];

    //si a les droits secutt, peut parler à tout le monde

    //si n'a pas de droit admin

    //$query = "INSERT INTO chat (id_expediteur, id_recepteur, date, message) VALUES ('".$_SESSION['id']."', '0', NOW( ), '".$message."')";
    //$this->sql->query($query);

    //$value = $_SESSION['id'].', 0, '.time().', '.$message;
    //$this->sql->insert('chat', 'id_expediteur, id_recepteur, date, message', $_SESSION['id']."', '0', NOW( ), '".$message);
    }

    public function recup_message()
    {
        $id = $_SESSION['id'];
        $query = $this->sql->query('SELECT id_expediteur, id_recepteur, date, message, login FROM chat INNER JOIN utilisateur ON (chat.id_expediteur = utilisateur.id) WHERE id_expediteur='.$id.' OR id_recepteur='.$id);

//SELECT u1.login as expediteur,u2.login as recepteur,c.message, c.id FROM `chat` c , `utilisateur` u1, `utilisateur` u2 WHERE c.id_expediteur=u1.id AND c.id_recepteur=u2.id AND (c.id_expediteur=1 OR c.id_recepteur=1)

    //return $resultat;
    echo '<ul>';
        while ($value = mysql_fetch_assoc($query)) {
            echo '<li>De <strong>'.$value['login'].'</strong> à <strong>'.$value['id_recepteur'].'</strong> <em>'.date('H:i:s', $value['date']).'</em>'.$value['message'].'</li>';
        }
        echo '</ul>';
    }
}
