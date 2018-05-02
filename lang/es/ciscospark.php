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
 * Ciscospark strings
 *
 * @package    mod
 * @subpackage ciscospark
 * @copyright  2018 Diego Fernando Nieto (diegofn@me.com)
 * @author     Diego Fernando Nieto (diegofn@me.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['modulename']       = 'Cisco Spark';
$string['modulename_help']
                            = 'El modulo de actividades de Cisco Spark permite la comunicación colaborativa utilizando los espacios de Cisco Spark.

Estos espacios colaborativos son accedidos a través de Moodle o las aplicaciones de Cisco Spark en dispositivos móviles, PC y Mac y permite trabajar, intercambiar información y comunicarse en cualquier lugar, a toda hora y desde cualquier tipo de dispositivo.

Esta actividad de Moodle puede ser configurada para crear un solo espacio de discusión, incluir todos los participantes de la clase o crear un conjunto de espacios diferentes para los diferentes grupos de miembros del curso.

Cada curso de Moodle será representado en la interfaz Cisco Spark del profesor como un Equipo (Team) y contendrá todos los espacios del curso. El profesor puede garantizar o eliminar el acceso de cada estudiante a un espacio en cualquier momento.

El módulo de actividades de Cisco Spark para Moodle es la solución ideal para gestionar espacios de comunicación, intercambio de información y colaboración para equipos de trabajo, proyectos asesorados y demás actividades del curso.

La integración entre Cisco Spark en Moodle garantiza la trazabilidad de cada uno de los espacios de discusión relacionados al curso y a los proyectos.

Las características de comunicación y colaboración de Cisco Spark como video conferencia, compartir archivos y tableros expanden las posibilidades de enseñanza en la edad moderna.';

$string['modulename_link']  = 'mod/ciscospark/view';
$string['modulenameplural'] = 'Cisco Spark';

$string['pluginadministration'] = 'Administración de Cisco Spark';
$string['pluginname']           = 'Cisco Spark';
$string['crontask']             = 'Actualizar tokens de Cisco Spark tokens';
$string['sync_rooms']           = 'Sincronización de espacios';
$string['use_groups']           = 'Usar grupos del curso';
$string['remove_user_message']  = 'No tiene acceso al espacio de discusión';

//capabilities
$string['ciscospark:view']            = 'Ver actividad';
$string['ciscospark:addinstance']     = 'Agregar una instancia de Cisco Spark';
$string['ciscospark:viewhiddenrooms'] = 'Ver espacios ocultos';
$string['ciscospark:hiderooms']       = 'Ocultar espacios';

//specific strings
$string['noavailablerooms'] = 'No tiene permiso a ningún espacio de discusión';
$string['showhide']         = 'Mostrar / Ocultar espacio de discusión';
$string['enterroom']        = 'Ir al espacio Cisco';

//settings
$string['setting_clientid']     = 'Cisco Spark integration Client ID';
$string['setting_clientsecret'] = 'Cisco Spark integration Client Secret';
$string['setting_bottoken']     = 'Bot access token';
$string['setting_botemail']     = 'Bot email';
