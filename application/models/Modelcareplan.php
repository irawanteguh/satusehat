<?php
    class Modelcareplan extends CI_Model{

        function careplanRJ($env){
            $query =
                    "
                        WITH ENCOUNTER AS (
                            SELECT PASIEN_ID, EPISODE_ID, RESOURCE_ID, POLI_ID, DOKTER_ID,
                                ROW_NUMBER() OVER (PARTITION BY PASIEN_ID, EPISODE_ID ORDER BY CREATED_DATE) AS RN
                            FROM SR01_SATUSEHAT_TRANSAKSI A
                            WHERE A.LOKASI_ID = '001'
                            AND A.AKTIF = '1'
                            AND A.JENIS = '1'
                            AND A.RESOURCE_TYPE = 'Encounter'
                            AND A.ENVIRONMENT = '".$env."'
                            AND A.EPISODE_ID NOT IN (
                                SELECT EPISODE_ID
                                FROM SR01_SATUSEHAT_TRANSAKSI
                                WHERE LOKASI_ID     = '001'
                                AND   AKTIF         = '1'
                                AND   JENIS         = '1'
                                AND   RESOURCE_TYPE = 'CarePlan'
                                AND   ENVIRONMENT   = '".$env."'
                                AND   PASIEN_ID     = A.PASIEN_ID
                                AND   EPISODE_ID    = A.EPISODE_ID
                            )
                        ),
                        ENCOUNTER_FIRST AS (
                            SELECT PASIEN_ID, EPISODE_ID, RESOURCE_ID, POLI_ID, DOKTER_ID
                            FROM ENCOUNTER
                            WHERE RN = 1
                        ),
                        DATAVALUE AS (
                            SELECT PASIEN_ID, EPISODE_ID, TRANS_ID, CREATED_BY, CREATED_DATE, P
                            FROM WEB_CO_DIAGNOSA_DT
                            WHERE SHOW_ITEM = '1'
                        )

                        SELECT A.PASIEN_ID, A.EPISODE_ID, A.RESOURCE_ID, A.POLI_ID, A.DOKTER_ID,
                            B.TRANS_ID, B.P AS CAREPLAN,
                            TO_CHAR(B.CREATED_DATE - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(B.CREATED_DATE - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS PERIODSTART,
                            
                            C.SATUSEHAT_ID AS PATIENTID,
                            C.INT_PASIEN_ID AS PATIENTMR,
                            C.NAMA AS PATIENTNAME,
                            
                            D.IHS_ID AS PRACTITIONERID,
                            UPPER(D.NAMA) AS PRACTITIONERNAME,
                            
                            E.SATUSEHAT_ID AS LOCATIONID,
                            E.NAME AS LOCATIONNAME
                            
                        FROM ENCOUNTER_FIRST A
                        JOIN DATAVALUE B
                        ON B.PASIEN_ID = A.PASIEN_ID
                        AND B.EPISODE_ID = A.EPISODE_ID
                        JOIN SR01_GEN_PASIEN_MS C
                        ON C.LOKASI_ID = '001'
                        AND C.AKTIF = '1'
                        AND C.PASIEN_ID = A.PASIEN_ID
                        JOIN SR01_GEN_USER_DATA D
                        ON D.LOKASI_ID = '001'
                        AND D.AKTIF = '1'
                        AND D.IHS_ID IS NOT NULL
                        AND D.IHS_ID <> 'NOT FOUND'
                        AND UPPER(D.DOKTER_ID) = UPPER(B.CREATED_BY)
                        JOIN SR01_SATUSEHAT_LOCATION E
                        ON E.LOKASI_ID = '001'
                        AND E.AKTIF = '1'
                        AND E.STATUS = 'active'
                        AND E.VALUE = A.POLI_ID
                        FETCH FIRST 10 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }
        
    }
?>
