<?php
/**
 *    Arcane Nexus, plugin for WordPress
 * 
 *    Copyright (C) 2020  Dmitry Shumilin (dmitri.shumilinn@yandex.ru)
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
class ANModel
{

    private $db;
    private $db_name;

    public function __construct($database = null)
    {
        
        if (!empty($database) && is_string($database)) {

            $this->db_name = $database;
            $this->db = new wpdb(DB_USER, DB_PASSWORD, $this->db_name, DB_HOST);

            if (!empty($this->db->error)) wp_die($this->db->error);

        } else {

            global $wpdb;

            $this->db = $wpdb;
            $this->db_name = DB_NAME;

        }

    }

    public function generate_random_string(int $length = 50)
    {

        if ($length <= 0) $length = 50;

        $band = array_merge(range('a', 'z'), range(0, 9));

        $key = '';

        for ($i = 0; $i < $length; $i++) {

            $key .= $band[rand(0, count($band) - 1)];

        }

        return $key;

    }

    public function generate_htaccess(string $plugin_dir)
    {

        $htaccess = "\n".'Redirect 301 /wp-content/plugins/'.$plugin_dir.'/key '.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/wp-content/plugins/'.$plugin_dir.'/'."\n";

        return $htaccess;

    }

    public function installation()
    {

        $result = ($this->tables_drop()) and ($this->tables_create());

        return $result;

    }

    public function tables_drop()
    {

        $query = $this->db->query("DROP TABLE IF EXISTS ARCANE_NEXUS_chain");

        return $query;

    }

    public function tables_create()
    {

        $query = $this->db->query("CREATE TABLE `ARCANE_NEXUS_chain` (`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT, `uri` VARCHAR (50) NOT NULL, `file` VARCHAR(25) NOT NULL, `position` VARCHAR(10) NOT NULL, PRIMARY KEY (`id`), UNIQUE INDEX `uri` (`uri`)) COLLATE='utf8_general_ci' AUTO_INCREMENT=0");

        return $query;

    }

    public function get_nexus(int $id = 0)
    {

        if (empty($id)) $query = "SELECT * FROM ".$this->db_name.".ARCANE_NEXUS_chain AS t ORDER BY t.id ASC";
        elseif ($id < 0) $query = $this->db->prepare("SELECT * FROM ".$this->db_name.".ARCANE_NEXUS_chain AS t ORDER BY t.id ASC LIMIT %d", (int)(substr($id, 1)));
        else $query = $this->db->prepare("SELECT * FROM ".$this->db_name.".ARCANE_NEXUS_chain AS t WHERE t.id = %d", $id);

        $get_nexus = $this->db->get_results($query, ARRAY_A);

        if (count($get_nexus) > 0) {

            $result = [];

            foreach ($get_nexus as $row) {
                
                $result[$row['id']] = [
                    'uri' => stripslashes($row['uri']),
                    'file' => $row['file'],
                    'position' => $row['position']
                ];

            }

        } else $result = false;

        return $result;

    }

    public function update_nexus(int $id, string $uri, string $file, string $position)
    {

        if ($id > 0) $result = $this->db->query($this->db->prepare("UPDATE ".$this->db_name.".ARCANE_NEXUS_chain AS t SET t.uri = %s, t.file = %s, t.position = %s WHERE t.id = %d", addslashes(trim($uri)), $file, $position, $id));
        else $result = false;

        return $result;

    }

    public function insert_nexus(string $uri, string $file, string $position)
    {

        $result = $this->db->query($this->db->prepare("INSERT INTO ".$this->db_name.".ARCANE_NEXUS_chain (`uri`, `file`, `position`) VALUES (%s, %s, %s)", addslashes(trim($uri)), $file, $position));

        return $result;

    }

    public function delete_nexus(int $id)
    {

        $result = $this->db->query($this->db->prepare("DELETE FROM ".$this->db_name.".ARCANE_NEXUS_chain WHERE id = %d", $id));

        return $result;

    }

}
