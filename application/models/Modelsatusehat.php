<?php
    class Modelsatusehat extends CI_Model{
        function insertdata($data){           
            $sql =   $this->db->insert("SR01_SATUSEHAT_TRANSAKSI",$data);
            return $sql;
        }

        function insertdatamedication($data){           
            $sql =   $this->db->insert("SR01_SATUSEHAT_MEDICATION",$data);
            return $sql;
        }

        // function cekdatasatusehat($env,$episodeid,$resourcetype,$identifier){
        //     $query =
        //             "
        //                 SELECT A.RESOURCE_ID
        //                 FROM SR01_SATUSEHAT_TRANSAKSI A
        //                 WHERE A.LOKASI_ID='001'
        //                 AND   A.AKTIF='1'
        //                 AND   A.ENVIRONMENT='".$env."'
        //                 AND   A.RESOURCE_TYPE='".$resourcetype."'
        //                 AND   A.EPISODE_ID='".$episodeid."'
        //                 AND   A.IDENTIFIER='".$identifier."'
        //             ";

		// 	$recordset = $this->db->query($query);
		// 	$recordset = $recordset->result();
		// 	return $recordset;
        // }

        function cekdataresouce($env,$resourcetype,$resourceid){
            $query =
                    "
                        SELECT A.RESOURCE_ID
                        FROM SR01_SATUSEHAT_TRANSAKSI A
                        WHERE A.LOKASI_ID='001'
                        AND   A.AKTIF='1'
                        AND   A.ENVIRONMENT='".$env."'
                        AND   A.RESOURCE_TYPE='".$resourcetype."'
                        AND   A.RESOURCE_ID='".$resourceid."'
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

    }

?>