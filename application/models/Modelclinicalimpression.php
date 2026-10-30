<?php
    class Modelclinicalimpression extends CI_Model{

        function clinicalimpressionRJIGD($env){
            $query =
                    "
                        WITH ENCOUNTER AS (
                            SELECT PASIEN_ID, EPISODE_ID, RESOURCE_ID, POLI_ID, DOKTER_ID,
                                ROW_NUMBER() OVER (PARTITION BY PASIEN_ID, EPISODE_ID ORDER BY CREATED_DATE) AS RN
                            FROM SR01_SATUSEHAT_TRANSAKSI A
                            WHERE A.LOKASI_ID = '001'
                            AND A.AKTIF = '1'
                            AND A.JENIS IN ('1','2')
                            AND A.RESOURCE_TYPE = 'Encounter'
                            AND A.ENVIRONMENT = '".$env."'
                            AND A.EPISODE_ID NOT IN (
                                SELECT EPISODE_ID
                                FROM SR01_SATUSEHAT_TRANSAKSI
                                WHERE LOKASI_ID     = '001'
                                AND   AKTIF         = '1'
                                AND   RESOURCE_TYPE = 'ClinicalImpression'
                                AND   ENVIRONMENT   = 'production'
                                AND   PASIEN_ID     = A.PASIEN_ID
                                AND   EPISODE_ID    = A.EPISODE_ID
                            )
                        ),
                        ENCOUNTER_FIRST AS (
                            SELECT PASIEN_ID, EPISODE_ID, RESOURCE_ID, POLI_ID, DOKTER_ID
                            FROM ENCOUNTER
                            WHERE RN = 1
                        )

                        SELECT A.PASIEN_ID, A.EPISODE_ID, A.RESOURCE_ID, A.POLI_ID, A.DOKTER_ID,
                            DIAG.CONDITION,
                            ASSESS.A AS ASSESSMENT,

                            TO_CHAR(ASSESS.CREATED_DATE - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(ASSESS.CREATED_DATE - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS PERIODSTART,

                            C.SATUSEHAT_ID AS PATIENTID,
                            C.INT_PASIEN_ID AS PATIENTMR,
                            C.NAMA AS PATIENTNAME,
                                                    
                            D.IHS_ID AS PRACTITIONERID,
                            UPPER(D.NAMA) AS PRACTITIONERNAME,
                                                    
                            E.SATUSEHAT_ID AS LOCATIONID,
                            E.NAME AS LOCATIONNAME       
                                                                                        
                        FROM ENCOUNTER_FIRST A
                        JOIN SR01_GEN_PASIEN_MS C
                        ON C.LOKASI_ID = '001'
                        AND C.AKTIF = '1'
                        AND C.PASIEN_ID = A.PASIEN_ID
                        JOIN SR01_GEN_USER_DATA D
                        ON D.LOKASI_ID = '001'
                        AND D.AKTIF = '1'
                        AND D.IHS_ID IS NOT NULL
                        AND D.IHS_ID <> 'NOT FOUND'
                        AND UPPER(D.DOKTER_ID) = UPPER(A.DOKTER_ID)
                        JOIN SR01_SATUSEHAT_LOCATION E
                        ON E.LOKASI_ID = '001'
                        AND E.AKTIF = '1'
                        AND E.STATUS = 'active'
                        AND E.VALUE = A.POLI_ID

                        OUTER APPLY (
                            SELECT
                                LISTAGG(
                                    R.RESOURCE_ID,','
                                ) WITHIN GROUP (
                                    ORDER BY RESOURCE_ID
                                ) AS CONDITION
                            FROM SR01_SATUSEHAT_TRANSAKSI R
                            WHERE R.LOKASI_ID = '001'
                            AND   R.AKTIF='1'
                            AND   R.RESOURCE_TYPE='Condition'
                            AND   R.ENVIRONMENT='".$env."'
                            AND   R.PASIEN_ID=A.PASIEN_ID
                            AND   R.EPISODE_ID=A.EPISODE_ID
                            
                        ) DIAG

                        OUTER APPLY (
                            SELECT S.A, S.CREATED_DATE
                            FROM WEB_CO_DIAGNOSA_DT S
                            WHERE S.SHOW_ITEM = '1'
                            AND S.PASIEN_ID = A.PASIEN_ID
                            AND S.EPISODE_ID = A.EPISODE_ID
                        ) ASSESS

                        FETCH FIRST 10 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }
        
        
    }
?>
