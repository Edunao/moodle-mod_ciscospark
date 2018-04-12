<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Ciscospark fr strings
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2017 Edunao SAS (contact@edunao.com)
 * @author     Adrien Jamot <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['modulename']       = 'Cisco Spark';
$string['modulename_help']
                            = 'Le module d\'activité Cisco Spark permet de disposer d’espaces collaboratifs de communication sur Spark.
Ces espaces collaboratifs sont accessibles dans ou en dehors de Moodle au travers de clients Mobile, MAC ou PC. Ils permettent de pouvoir continuer à échanger, travailler et communiquer partout, tout le temps, depuis n\'importe quel type de terminal. 

Cette activité peut regrouper tous les participants d\'un cours dans un seul espace d\'échanges ou dans des espaces séparés pour chaque groupe du cours. 

Pour l\'enseignant, chaque cours Moodle représente une équipe Spark au sein de laquelle sont regroupés les différents espaces. À tout moment, l’enseignant peut activer ou désactiver l\'accès aux différents espaces. 
L\'activité Cisco Spark pour Moodle a de nombreuses utilisations, comme : 
- la création d\'espaces d\'échanges et de collaboration pour : 
   - des projets d\'équipe 
   - des activités collaboratives (Exercices, TP, TD) 
- la connexion directe entre Moodle et Cisco Spark, pilotée dans Moodle
- l\'accès dans Cisco Spark, a certaines fonctionnalités de classe virtuelle (vidéoconférence, tableau de bord)';
$string['modulename_link']  = 'mod/ciscospark/view';
$string['modulenameplural'] = 'Cisco Spark';

$string['pluginadministration'] = 'Cisco Spark administration';
$string['pluginname']           = 'Cisco Spark';
$string['crontask']             = 'Rafraichir les tokens Spark';
$string['sync_rooms']           = 'Synchronisation des espaces';
$string['use_groups']           = 'Utiliser les groupes du cours';
$string['remove_user_message']  = 'Vous ne pouvez pas accéder à cet espace';

//capabilities
$string['ciscospark:view']            = 'Voir l\'activité';
$string['ciscospark:addinstance']     = 'Ajouter une instance de Cisco Spark';
$string['ciscospark:viewhiddenrooms'] = 'Voir les espaces cachés';
$string['ciscospark:hiderooms']       = 'Cacher les espaces';

//specific strings
$string['noavailablerooms'] = 'Aucun espace disponible';
$string['showhide']         = 'Afficher/Cacher l\'espace';
$string['enterroom']        = 'Accéder à l\'espace Spark';

//settings
$string['setting_clientid']     = 'Cisco Spark integration Client ID';
$string['setting_clientsecret'] = 'Cisco Spark integration Client Secret';
$string['setting_bottoken']     = 'Bot access token';
$string['setting_botemail']     = 'Bot email';
