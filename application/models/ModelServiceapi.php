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

        function checkcodeexclude($kode){
            $query =
                    "
                        SELECT A.KODE
                        FROM SR01_SATUSEHAT_ICD_EXCLUDE A
                        WHERE UPPER(A.KODE)=UPPER('".$kode."')
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

        function codeexclude($data){           
            $sql =   $this->db->insert("SR01_SATUSEHAT_ICD_EXCLUDE",$data);
            return $sql;
        }

    }