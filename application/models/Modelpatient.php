<?php
    class Modelpatient extends CI_Model{

        function datapasien(){
           $query =
                    "
                        SELECT *
                        FROM (
                            -- Bagian 1: SATUSEHAT_ID IS NULL (dahulukan)
                            SELECT A.PASIEN_ID,
                                A.NO_IDENTITAS,
                                A.NAMA,
                                TO_CHAR(A.SATUSEHAT_DATE,'DD.MM.YYYY HH24:MI:SS') AS SATUSEHAT_DATE,
                                A.SATUSEHAT_ID,
                                1 AS PRIORITY,
                                1 AS SORT_ORDER
                            FROM SR01_GEN_PASIEN_MS A
                            WHERE A.LOKASI_ID = '001'
                            AND A.AKTIF = '1'
                            AND A.ACC_SATUSEHAT = 'Y'
                            AND A.SATUSEHAT_ID IS NULL
                            AND A.NO_IDENTITAS IS NOT NULL
                            AND A.NO_IDENTITAS NOT IN ('\','-','`')
                            AND LENGTH(A.NO_IDENTITAS) = 16

                            UNION ALL

                            -- Bagian 2: SATUSEHAT_ID X / NOT FOUND (acak)
                            SELECT A.PASIEN_ID,
                                A.NO_IDENTITAS,
                                A.NAMA,
                                TO_CHAR(A.SATUSEHAT_DATE,'DD.MM.YYYY HH24:MI:SS') AS SATUSEHAT_DATE,
                                A.SATUSEHAT_ID,
                                2 AS PRIORITY,
                                DBMS_RANDOM.VALUE AS SORT_ORDER
                            FROM SR01_GEN_PASIEN_MS A
                            WHERE A.LOKASI_ID = '001'
                            AND A.AKTIF = '1'
                            AND A.ACC_SATUSEHAT = 'Y'
                            AND A.SATUSEHAT_ID IN ('X','NOT FOUND')
                            AND A.NO_IDENTITAS IS NOT NULL
                            AND A.NO_IDENTITAS NOT IN ('\','-','`')
                            AND LENGTH(A.NO_IDENTITAS) = 16
                        )
                        ORDER BY PRIORITY, SORT_ORDER
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
