<?php
    class ModelObservation extends CI_Model{

        function anamnesaRJ($env){
            $query =
                    "
                        WITH ENCOUNTER AS (
                            SELECT T.PASIEN_ID, T.EPISODE_ID, T.RESOURCE_ID, T.POLI_ID, T.DOKTER_ID,
                                ROW_NUMBER() OVER (PARTITION BY T.PASIEN_ID, T.EPISODE_ID ORDER BY T.CREATED_DATE) AS RN
                            FROM SR01_SATUSEHAT_TRANSAKSI T
                            WHERE T.LOKASI_ID = '001'
                            AND T.AKTIF = '1'
                            AND T.JENIS = '1'
                            AND T.RESOURCE_TYPE = 'Encounter'
                            AND T.ENVIRONMENT = '".$env."'
                        ),
                        ENCOUNTER_FIRST AS (
                            SELECT PASIEN_ID, EPISODE_ID, RESOURCE_ID, POLI_ID, DOKTER_ID
                            FROM ENCOUNTER
                            WHERE RN = 1
                        ),
                        BASEDATA AS (
                            SELECT A.TRANS_ID, A.PASIEN_ID, A.EPISODE_ID, A.CREATED_BY, A.CREATED_DATE, TV_FREK_NADI, TV_FREK_NAFAS, TV_TEKANAN_DARAH, TV_TEKANAN_DARAH2, TV_SUHU, ANT_BB, ANT_TB, ANT_IMT
                            FROM SR01_MED_ANAMAWAL A
                            WHERE A.AKTIF    = '1'
                            AND TRUNC(A.CREATED_DATE) >= (
                                SELECT TRUNC(P.SATUSEHAT_DATE)
                                FROM SR01_GEN_PASIEN_MS P
                                WHERE P.LOKASI_ID = '001'
                                    AND P.AKTIF = '1'
                                    AND P.ACC_SATUSEHAT = 'Y'
                                    AND P.SATUSEHAT_ID IS NOT NULL
                                    AND P.PASIEN_ID = A.PASIEN_ID
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_GEN_USER_DATA U
                                WHERE U.LOKASI_ID = '001'
                                    AND U.AKTIF = '1'
                                    AND U.IHS_ID IS NOT NULL
                                    AND U.IHS_ID <> 'NOT FOUND'
                                    AND 'SIRS01_' || UPPER(U.USER_ID) = UPPER(A.CREATED_BY)
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_SATUSEHAT_TRANSAKSI T
                                WHERE T.LOKASI_ID = '001'
                                    AND T.AKTIF = '1'
                                    AND T.JENIS = '1'
                                    AND T.RESOURCE_TYPE = 'Encounter'
                                    AND T.ENVIRONMENT = '".$env."'
                                    AND T.PASIEN_ID = A.PASIEN_ID
                                    AND T.EPISODE_ID = A.EPISODE_ID
                            )
                            AND NOT EXISTS (
                                SELECT 1
                                FROM SR01_SATUSEHAT_TRANSAKSI T
                                WHERE T.LOKASI_ID = '001'
                                    AND T.AKTIF = '1'
                                    AND T.RESOURCE_TYPE = 'Observation'
                                    AND T.JENIS = '1'
                                    AND T.ENVIRONMENT = '".$env."'
                                    AND T.PASIEN_ID = A.PASIEN_ID
                                    AND T.EPISODE_ID = A.EPISODE_ID
                            )
                        )
                        SELECT A.PASIEN_ID, A.EPISODE_ID, A.TRANS_ID, TV_FREK_NADI, TV_FREK_NAFAS, TV_TEKANAN_DARAH, TV_TEKANAN_DARAH2, TV_SUHU, ANT_BB, ANT_TB, ANT_IMT,
                            E.RESOURCE_ID, E.POLI_ID, E.DOKTER_ID,
                            TO_CHAR(A.CREATED_DATE - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(A.CREATED_DATE - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS PERIODSTART,
                                                    
                            P.SATUSEHAT_ID AS PATIENTID,
                            P.INT_PASIEN_ID AS PATIENTMR,
                            P.NAMA AS PATIENTNAME,
                                                    
                            U.IHS_ID AS PRACTITIONERID,
                            UPPER(U.NAMA) AS PRACTITIONERNAME,
                            L.SATUSEHAT_ID AS LOCATIONID,
                            L.NAME AS LOCATIONNAME
                                                    
                        FROM BASEDATA A
                        JOIN ENCOUNTER_FIRST E
                        ON E.PASIEN_ID = A.PASIEN_ID
                        AND E.EPISODE_ID = A.EPISODE_ID
                        LEFT JOIN SR01_GEN_PASIEN_MS P
                        ON P.LOKASI_ID = '001'
                        AND P.AKTIF = '1'
                        AND P.PASIEN_ID = A.PASIEN_ID
                        LEFT JOIN SR01_GEN_USER_DATA U
                        ON U.LOKASI_ID = '001'
                        AND U.AKTIF = '1'
                        AND U.IHS_ID IS NOT NULL
                        AND U.IHS_ID <> 'NOT FOUND'
                        AND 'SIRS01_' || UPPER(U.USER_ID) = UPPER(A.CREATED_BY)
                        LEFT JOIN SR01_SATUSEHAT_LOCATION L
                        ON L.LOKASI_ID = '001'
                        AND L.AKTIF = '1'
                        AND L.STATUS = 'active'
                        AND L.VALUE = E.POLI_ID
                        FETCH FIRST 10 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

        function pengkajianIGD($env){
            $query =
                    "
                        WITH ENCOUNTER AS (
                            SELECT T.PASIEN_ID, T.EPISODE_ID, T.RESOURCE_ID, T.POLI_ID, T.DOKTER_ID,
                                ROW_NUMBER() OVER (PARTITION BY T.PASIEN_ID, T.EPISODE_ID ORDER BY T.CREATED_DATE) AS RN
                            FROM SR01_SATUSEHAT_TRANSAKSI T
                            WHERE T.LOKASI_ID = '001'
                            AND T.AKTIF = '1'
                            AND T.JENIS = '2'
                            AND T.RESOURCE_TYPE = 'Encounter'
                            AND T.ENVIRONMENT = '".$env."'
                        ),
                        ENCOUNTER_FIRST AS (
                            SELECT PASIEN_ID, EPISODE_ID, RESOURCE_ID, POLI_ID, DOKTER_ID
                            FROM ENCOUNTER
                            WHERE RN = 1
                        ),
                        MED_PRWT AS (
                            SELECT TRANS_ID, PASIEN_ID, EPISODE_ID
                            FROM SR01_MED_PRWT_TR
                            WHERE LOKASI_ID='001'
                            AND   AKTIF='1'
                            AND   POLI_ID='UGD01'
                        ),
                        BASEDATA AS (
                            SELECT A.TRANS_ID, A.CREATED_BY, A.CREATED_DATE, TINGKAT_GAWAT, CARA_MASUK, BR_FREKUENSI_NAFAS, CIR_TKAN_DARAH, CIR_TKAN_DARAH2, CIR_NADI_TERABA, SUHU_AXILA,
                                Q.PASIEN_ID, Q.EPISODE_ID
                            FROM SR01_MED_KAJIUGD A
                            JOIN MED_PRWT Q
                            ON Q.TRANS_ID = A.TRANS_ID
                            WHERE A.AKTIF    = '1'
                            AND TRUNC(A.CREATED_DATE) >= (
                                SELECT TRUNC(P.SATUSEHAT_DATE)
                                FROM SR01_GEN_PASIEN_MS P
                                WHERE P.LOKASI_ID = '001'
                                    AND P.AKTIF = '1'
                                    AND P.ACC_SATUSEHAT = 'Y'
                                    AND P.SATUSEHAT_ID IS NOT NULL
                                    AND P.PASIEN_ID = Q.PASIEN_ID
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_GEN_USER_DATA U
                                WHERE U.LOKASI_ID = '001'
                                    AND U.AKTIF = '1'
                                    AND U.IHS_ID IS NOT NULL
                                    AND U.IHS_ID <> 'NOT FOUND'
                                    AND 'SIRS01_' || UPPER(U.USER_ID) = UPPER(A.CREATED_BY)
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_SATUSEHAT_TRANSAKSI T
                                WHERE T.LOKASI_ID = '001'
                                    AND T.AKTIF = '1'
                                    AND T.JENIS = '2'
                                    AND T.RESOURCE_TYPE = 'Encounter'
                                    AND T.ENVIRONMENT = '".$env."'
                                    AND T.PASIEN_ID = P.PASIEN_ID
                                    AND T.EPISODE_ID = P.EPISODE_ID
                            )
                            AND NOT EXISTS (
                                SELECT 1
                                FROM SR01_SATUSEHAT_TRANSAKSI T
                                WHERE T.LOKASI_ID = '001'
                                    AND T.AKTIF = '1'
                                    AND T.RESOURCE_TYPE = 'Observation'
                                    AND T.JENIS = '3'
                                    AND T.ENVIRONMENT = '".$env."'
                                    AND T.PASIEN_ID = P.PASIEN_ID
                                    AND T.EPISODE_ID = P.EPISODE_ID
                            )
                        )
                        SELECT A.PASIEN_ID, A.EPISODE_ID, A.TRANS_ID, TINGKAT_GAWAT, CARA_MASUK, BR_FREKUENSI_NAFAS, CIR_TKAN_DARAH, CIR_TKAN_DARAH2, CIR_NADI_TERABA, SUHU_AXILA,
                            E.RESOURCE_ID, E.POLI_ID, E.DOKTER_ID,
                            TO_CHAR(A.CREATED_DATE - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(A.CREATED_DATE - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS PERIODSTART,
                                                                            
                            P.SATUSEHAT_ID AS PATIENTID,
                            P.INT_PASIEN_ID AS PATIENTMR,
                            P.NAMA AS PATIENTNAME,
                                                                            
                            U.IHS_ID AS PRACTITIONERID,
                            UPPER(U.NAMA) AS PRACTITIONERNAME,
                            L.SATUSEHAT_ID AS LOCATIONID,
                            L.NAME AS LOCATIONNAME
                                                                            
                        FROM BASEDATA A
                        JOIN ENCOUNTER_FIRST E
                        ON E.PASIEN_ID = A.PASIEN_ID
                        AND E.EPISODE_ID = A.EPISODE_ID
                        LEFT JOIN SR01_GEN_PASIEN_MS P
                        ON P.LOKASI_ID = '001'
                        AND P.AKTIF = '1'
                        AND P.PASIEN_ID = A.PASIEN_ID
                        LEFT JOIN SR01_GEN_USER_DATA U
                        ON U.LOKASI_ID = '001'
                        AND U.AKTIF = '1'
                        AND U.IHS_ID IS NOT NULL
                        AND U.IHS_ID <> 'NOT FOUND'
                        AND 'SIRS01_' || UPPER(U.USER_ID) = UPPER(A.CREATED_BY)
                        LEFT JOIN SR01_SATUSEHAT_LOCATION L
                        ON L.LOKASI_ID = '001'
                        AND L.AKTIF = '1'
                        AND L.STATUS = 'active'
                        AND L.VALUE = E.POLI_ID
                        FETCH FIRST 10 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

        function psikologisIGD($env){
            $query =
                    "
                        WITH ENCOUNTER AS (
                            SELECT T.PASIEN_ID, T.EPISODE_ID, T.RESOURCE_ID, T.POLI_ID, T.DOKTER_ID,
                                ROW_NUMBER() OVER (PARTITION BY T.PASIEN_ID, T.EPISODE_ID ORDER BY T.CREATED_DATE) AS RN
                            FROM SR01_SATUSEHAT_TRANSAKSI T
                            WHERE T.LOKASI_ID = '001'
                            AND T.AKTIF = '1'
                            AND T.JENIS = '2'
                            AND T.RESOURCE_TYPE = 'Encounter'
                            AND T.ENVIRONMENT = '".$env."'
                        ),
                        ENCOUNTER_FIRST AS (
                            SELECT PASIEN_ID, EPISODE_ID, RESOURCE_ID, POLI_ID, DOKTER_ID
                            FROM ENCOUNTER
                            WHERE RN = 1
                        ),
                        BASEDATA AS (
                            SELECT A.TRANS_ID, A.PASIEN_ID, A.EPISODE_ID, A.PSIKOLOGI, A.CREATED_BY, A.CREATED_DATE
                            FROM SR01_MED_TRIAGE_HD_V2 A
                            WHERE A.AKTIF    = '1'
                            AND A.ISMANUAL = 'E'
                            AND TRUNC(A.CREATED_DATE) >= (
                                SELECT TRUNC(P.SATUSEHAT_DATE)
                                FROM SR01_GEN_PASIEN_MS P
                                WHERE P.LOKASI_ID = '001'
                                    AND P.AKTIF = '1'
                                    AND P.ACC_SATUSEHAT = 'Y'
                                    AND P.SATUSEHAT_ID IS NOT NULL
                                    AND P.PASIEN_ID = A.PASIEN_ID
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_GEN_USER_DATA U
                                WHERE U.LOKASI_ID = '001'
                                    AND U.AKTIF = '1'
                                    AND U.IHS_ID IS NOT NULL
                                    AND U.IHS_ID <> 'NOT FOUND'
                                    AND 'SIRS01_' || UPPER(U.USER_ID) = UPPER(A.CREATED_BY)
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_SATUSEHAT_TRANSAKSI T
                                WHERE T.LOKASI_ID = '001'
                                    AND T.AKTIF = '1'
                                    AND T.JENIS = '2'
                                    AND T.RESOURCE_TYPE = 'Encounter'
                                    AND T.ENVIRONMENT = '".$env."'
                                    AND T.PASIEN_ID = A.PASIEN_ID
                                    AND T.EPISODE_ID = A.EPISODE_ID
                            )
                            AND NOT EXISTS (
                                SELECT 1
                                FROM SR01_SATUSEHAT_TRANSAKSI T
                                WHERE T.LOKASI_ID = '001'
                                    AND T.AKTIF = '1'
                                    AND T.RESOURCE_TYPE = 'Observation'
                                    AND T.JENIS = '4'
                                    AND T.ENVIRONMENT = '".$env."'
                                    AND T.PASIEN_ID = A.PASIEN_ID
                                    AND T.EPISODE_ID = A.EPISODE_ID
                            )
                        )
                        SELECT A.PASIEN_ID, A.EPISODE_ID, A.TRANS_ID, A.PSIKOLOGI,
                            E.RESOURCE_ID, E.POLI_ID, E.DOKTER_ID,
                            TO_CHAR(A.CREATED_DATE - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(A.CREATED_DATE - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS PERIODSTART,
                            
                            P.SATUSEHAT_ID AS PATIENTID,
                            P.INT_PASIEN_ID AS PATIENTMR,
                            P.NAMA AS PATIENTNAME,
                            
                            U.IHS_ID AS PRACTITIONERID,
                            UPPER(U.NAMA) AS PRACTITIONERNAME,
                            L.SATUSEHAT_ID AS LOCATIONID,
                            L.NAME AS LOCATIONNAME
                            
                        FROM BASEDATA A
                        JOIN ENCOUNTER_FIRST E
                        ON E.PASIEN_ID = A.PASIEN_ID
                        AND E.EPISODE_ID = A.EPISODE_ID
                        LEFT JOIN SR01_GEN_PASIEN_MS P
                        ON P.LOKASI_ID = '001'
                        AND P.AKTIF = '1'
                        AND P.PASIEN_ID = A.PASIEN_ID
                        LEFT JOIN SR01_GEN_USER_DATA U
                        ON U.LOKASI_ID = '001'
                        AND U.AKTIF = '1'
                        AND U.IHS_ID IS NOT NULL
                        AND U.IHS_ID <> 'NOT FOUND'
                        AND 'SIRS01_' || UPPER(U.USER_ID) = UPPER(A.CREATED_BY)
                        LEFT JOIN SR01_SATUSEHAT_LOCATION L
                        ON L.LOKASI_ID = '001'
                        AND L.AKTIF = '1'
                        AND L.STATUS = 'active'
                        AND L.VALUE = E.POLI_ID
                        FETCH FIRST 10 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }
    }
?>
