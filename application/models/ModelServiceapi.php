<?php
    class Modelserviceapi extends CI_Model{

        function savelog($data){           
            $sql =   $this->db->insert("WEB_API_LOGS_OUT",$data);
            return $sql;
        }

        function saveissuelog($data){           
            $sql =   $this->db->insert("SR01_SATUSEHAT_ISSUE_LOG",$data);
            return $sql;
        }

    }

?>