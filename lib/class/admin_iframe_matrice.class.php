<?php

/*
 *
 * Auteur : Reivax <bernard.xav@gmail.com>
 * Modification : 15/05/11 par SoX <flosox@gmail.com>
 *
 * Description :
 *
 */

class admin_iframe_matrice
{
    private $sql;
    private $timeCourant;

    public function __construct()
    {
        $this->sql = new sql();
        $this->timeCourant = time();
    }

    public function retourneSelectTypeStock($valueEx = '')
    {
        $resulat = $this->sql->select('id, nom', 'type_stock', 'ORDER BY nom');
        $string = '<option value="">choix du stock</option>';
        foreach ($resulat as $value) {
            if ($value['id'] == $valueEx) {
                $string .= '<option value="'.$value['id'].'" selected="selected">'.$value['nom'].'</option>';
            } else {
                $string .= '<option value="'.$value['id'].'">'.$value['nom'].'</option>';
            }
        }

        return $string;
    }

    public function retourneSelectEspace($valueEx = '')
    {
        $resulat = $this->sql->select('id, nom, lieu', 'espace', 'ORDER BY nom');
        $string = '<option value="">déplacer vers</option>';
        foreach ($resulat as $value) {
            if ($value['id'] == $valueEx) {
                $string .= '<option value="'.$value['id'].'" selected="selected">'.$value['nom'].' - '.$value['lieu'].'</option>';
            } else {
                $string .= '<option value="'.$value['id'].'">'.$value['nom'].' - '.$value['lieu'].'</option>';
            }
        }

        return $string;
    }

    public function retourneSelectSimulBar($valueEx = '')
    {
        $resulat = $this->sql->select('id, nom, lieu', 'espace', 'ORDER BY nom');
        $string = '<option value="">voir...</option>';
        foreach ($resulat as $value) {
            if ($value['id'] == $valueEx) {
                $string .= '<option value="'.$value['id'].'" selected="selected">'.$value['nom'].' - '.$value['lieu'].'</option>';
            } else {
                $string .= '<option value="'.$value['id'].'">'.$value['nom'].' - '.$value['lieu'].'</option>';
            }
        }

        return $string;
    }

    public function table_stock($id)
    {
        $resultat = $this->sql->select('*', '(SELECT stock.id AS id_stock, stock.identifiant, SUBSTR(MAX(CONCAT(LPAD(parcours.id,6,\' \'),espace.nom)),7) as nom_now, SUBSTR(MAX(CONCAT(LPAD(parcours.id,6,\' \'),espace.lieu)),7) as lieu_now FROM stock LEFT JOIN parcours ON (parcours.id_stock = stock.id) LEFT JOIN espace ON (parcours.id_espace = espace.id) WHERE id_type_stock='.$id.' GROUP BY stock.id ORDER BY stock.id) as toto', 'WHERE nom_now = "#QG log" OR nom_now IS NULL');

    //rien
    if ($resultat == 0) {
        echo '<p>aucun stock</p>';
    }
    //ATTENTION il ce passe quoi quand yen a qu'un seul
    else {
        $i = 1;
        echo '<form name="matrice" ><table id="tableau_log_fut">';
        foreach ($resultat as $value) {
            echo '<td id=td_log_'.$value['id_stock'].' height=40>';
            echo "<input type='checkbox' id='check_log_".$value['id_stock']."' name='check_log_".$value['id_stock']."' value='".$value['id_stock']."' onclick=\"if(document.getElementById('check_log_".$value['id_stock']."').checked == true){document.getElementById('check_log_".$value['id_stock']."').checked=true;changeColor('td_log_".$value['id_stock']."')}else{document.getElementById('check_log_".$value['id_stock']."').checked=false;changeColor('td_log_".$value['id_stock']."')}\">";
            echo "<a href=# onclick=\"if(document.getElementById('check_log_".$value['id_stock']."').checked == true){document.getElementById('check_log_".$value['id_stock']."').checked=false;changeColor('td_log_".$value['id_stock']."')}else{document.getElementById('check_log_".$value['id_stock']."').checked=true;changeColor('td_log_".$value['id_stock']."')}\">".$value['identifiant'].'</a></td>';
//  				echo '<td><select id="select_espace_'.$value['id_stock'].'" class="select_espace_deplace" name="'.$value['id_stock'].'">'.$this->retourneSelectEspace().'</select></td>';
            if (($i % 15) == 0) {
                echo '</tr><tr>';
            }
            $i = $i + 1;
        }
        echo '</tr>';
        echo '</table></form>';
    }
    }

    public function tr_stock($id_stock, $id_espace)
    {
        $reste = $this->sql->select('reste', 'stock', "WHERE id='".$id_stock."'");

    //echo $reste[0];
    $this->sql->update('parcours', '`fin` = NOW()', '`id_stock`='.$id_stock.' ORDER BY id DESC LIMIT 1');

        $this->sql->insert('parcours', '`id_stock`, `id_espace`, `debut`, `quantite_debut`', "'".$id_stock."', '".$id_espace."', NOW( ), '".$reste[0]."'");

        $resultat = $this->sql->select('stock.id AS id_stock, stock.identifiant, UNIX_TIMESTAMP(stock.entame) as entame, UNIX_TIMESTAMP(stock.fin) as fin, stock.reste, parcours.id_espace, espace.nom, parcours.id AS id_parcours, COUNT(parcours.id) as nbr, espace.lieu, SUBSTR(MAX(CONCAT(LPAD(parcours.id,6,\' \'),espace.nom)),7) as nom_now, SUBSTR(MAX(CONCAT(LPAD(parcours.id,6,\' \'),espace.lieu)),7) as lieu_now', 'stock', 'LEFT JOIN parcours ON (parcours.id_stock = stock.id) LEFT JOIN espace ON (parcours.id_espace = espace.id) WHERE parcours.id_stock='.$id_stock.' GROUP BY stock.id ORDER BY stock.id');

    //echo '<pre>';
    //print_r($resultat);
    //echo '</pre>';

    $string = '';
        $string .= '<td>'.$resultat['identifiant'].'</td>';
        $string .= '<td>'.$this->traiteTimestamp($resultat['entame']).'</td>';
        $string .= '<td>'.$this->depuisTimestamp($resultat['entame']).'</td>';
        $string .= '<td>'.$this->traiteTimestamp($resultat['fin']).'</td>';
        $string .= '<td>'.$this->depuisTimestamp($resultat['fin']).'</td>';
        $string .= '<td>'.$resultat['reste'].'</td>';
        $string .= '<td>'.$resultat['nom_now'].'</td>';
        $string .= '<td>'.$resultat['lieu_now'].'</td>';
        $string .= '<td>'.$resultat['nbr'].'</td>';
        $string .= '<td>déplacé</td>';

        echo $string;
    }

    private function traiteTimestamp($timestamp)
    {
        if ($timestamp != 0) {
            $string = date('H:i', $timestamp);

            return $string;
        } else {
            return 'non';
        }
    }

    private function depuisTimestamp($timestamp)
    {
        if ($timestamp != 0) {
            $depuis = $this->timeCourant - $timestamp;
            $string = floor($depuis / 60).' min';

            return $string;
        } else {
            return '';
        }
    }
}
