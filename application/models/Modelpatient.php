<?php
    class Modelpatient extends CI_Model{

        function datapasien(){
           $query =
                    "
                        SELECT A.PASIEN_ID, NO_IDENTITAS, NAMA, TO_CHAR(SATUSEHAT_DATE,'DD.MM.YYYY HH24:MI:SS')SATUSEHAT_DATE, SATUSEHAT_ID
                        FROM SR01_GEN_PASIEN_MS A
                        WHERE A.LOKASI_ID='001'
                        AND   A.AKTIF='1'
                        AND   A.ACC_SATUSEHAT='Y'
                        AND   A.SATUSEHAT_ID IS NULL
                        AND   A.NO_IDENTITAS IS NOT NULL AND A.NO_IDENTITAS NOT IN ('\','-','`') AND LENGTH(A.NO_IDENTITAS)=16
                        UNION
                        SELECT A.PASIEN_ID, NO_IDENTITAS, NAMA, TO_CHAR(SATUSEHAT_DATE,'DD.MM.YYYY HH24:MI:SS')SATUSEHAT_DATE, SATUSEHAT_ID
                        FROM SR01_GEN_PASIEN_MS A
                        WHERE A.LOKASI_ID='001'
                        AND   A.AKTIF='1'
                        AND   A.ACC_SATUSEHAT='Y'
                        AND   A.SATUSEHAT_ID IN ('X','NOT FOUND')
                        AND   A.NO_IDENTITAS IS NOT NULL AND A.NO_IDENTITAS NOT IN ('\','-','`') AND LENGTH(A.NO_IDENTITAS)=16
                        ORDER BY SATUSEHAT_ID DESC, SATUSEHAT_DATE DESC
                        FETCH FIRST 10 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

        function updatedatapatient($pasienid, $data){           
            $sql =   $this->db->update("SR01_GEN_PASIEN_MS",$data,array("PASIEN_ID"=>$pasienid, "LOKASI_ID"=>"001", "AKTIF"=>"1"));
            return $sql;
        }
        

    }
?>
