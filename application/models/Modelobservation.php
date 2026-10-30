<?php
    class ModelObservation extends CI_Model{

        function anamnesaRJ($env){
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
                                AND   RESOURCE_TYPE = 'Observation'
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
                            SELECT PASIEN_ID, EPISODE_ID, TRANS_ID, CREATED_BY, CREATED_DATE, TV_FREK_NADI, TV_FREK_NAFAS, TV_TEKANAN_DARAH, TV_TEKANAN_DARAH2, TV_SUHU, ANT_BB, ANT_TB, ANT_IMT
                            FROM SR01_MED_ANAMAWAL
                            WHERE AKTIF = '1'
                        )

                        SELECT A.PASIEN_ID, A.EPISODE_ID, A.RESOURCE_ID, A.POLI_ID, A.DOKTER_ID,
                            B.TRANS_ID, TV_FREK_NADI, TV_FREK_NAFAS, TV_TEKANAN_DARAH, TV_TEKANAN_DARAH2, TV_SUHU, ANT_BB, ANT_TB, ANT_IMT,
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
                        AND 'SIRS01_' || UPPER(D.USER_ID) = UPPER(B.CREATED_BY)
                        LEFT JOIN SR01_SATUSEHAT_LOCATION E
                        ON E.LOKASI_ID = '001'
                        AND E.AKTIF = '1'
                        AND E.STATUS = 'active'
                        AND E.VALUE = A.POLI_ID
                        FETCH FIRST 5 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

        function pengkajianIGD($env){
            $query =
                    "
                        WITH ENCOUNTER AS (
                            SELECT PASIEN_ID, EPISODE_ID, RESOURCE_ID, POLI_ID, DOKTER_ID,
                                ROW_NUMBER() OVER (PARTITION BY PASIEN_ID, EPISODE_ID ORDER BY CREATED_DATE) AS RN
                            FROM SR01_SATUSEHAT_TRANSAKSI A
                            WHERE A.LOKASI_ID = '001'
                            AND A.AKTIF = '1'
                            AND A.JENIS = '2'
                            AND A.RESOURCE_TYPE = 'Encounter'
                            AND A.ENVIRONMENT = '".$env."'
                            AND A.EPISODE_ID NOT IN (
                                SELECT EPISODE_ID
                                FROM SR01_SATUSEHAT_TRANSAKSI
                                WHERE LOKASI_ID     = '001'
                                AND   AKTIF         = '1'
                                AND   JENIS         = '3'
                                AND   RESOURCE_TYPE = 'Observation'
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
                            SELECT A.TRANS_ID, A.CREATED_BY, A.CREATED_DATE, TINGKAT_GAWAT, CARA_MASUK, BR_FREKUENSI_NAFAS, CIR_TKAN_DARAH, CIR_TKAN_DARAH2, CIR_NADI_TERABA, SUHU_AXILA,
                                B.PASIEN_ID, B.EPISODE_ID
                            FROM SR01_MED_KAJIUGD A
                            JOIN SR01_MED_PRWT_TR B
                            ON B.TRANS_ID = A.TRANS_ID
                            WHERE A.AKTIF = '1'
                        )

                        SELECT A.PASIEN_ID, A.EPISODE_ID, A.RESOURCE_ID, A.POLI_ID, A.DOKTER_ID,
                            B.TRANS_ID, TINGKAT_GAWAT, CARA_MASUK, BR_FREKUENSI_NAFAS, CIR_TKAN_DARAH, CIR_TKAN_DARAH2, CIR_NADI_TERABA, SUHU_AXILA,
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
                        AND 'SIRS01_' || UPPER(D.USER_ID) = UPPER(B.CREATED_BY)
                        LEFT JOIN SR01_SATUSEHAT_LOCATION E
                        ON E.LOKASI_ID = '001'
                        AND E.AKTIF = '1'
                        AND E.STATUS = 'active'
                        AND E.VALUE = A.POLI_ID
                        FETCH FIRST 5 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

        function psikologisIGD($env){
            $query =
                    "
                        WITH ENCOUNTER AS (
                            SELECT PASIEN_ID, EPISODE_ID, RESOURCE_ID, POLI_ID, DOKTER_ID,
                                ROW_NUMBER() OVER (PARTITION BY PASIEN_ID, EPISODE_ID ORDER BY CREATED_DATE) AS RN
                            FROM SR01_SATUSEHAT_TRANSAKSI A
                            WHERE A.LOKASI_ID = '001'
                            AND A.AKTIF = '1'
                            AND A.JENIS = '2'
                            AND A.RESOURCE_TYPE = 'Encounter'
                            AND A.ENVIRONMENT = '".$env."'
                            AND A.EPISODE_ID NOT IN (
                                SELECT EPISODE_ID
                                FROM SR01_SATUSEHAT_TRANSAKSI
                                WHERE LOKASI_ID     = '001'
                                AND   AKTIF         = '1'
                                AND   JENIS         = '4'
                                AND   RESOURCE_TYPE = 'Observation'
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
                            SELECT PASIEN_ID, EPISODE_ID, TRANS_ID, CREATED_BY, CREATED_DATE, PSIKOLOGI
                            FROM SR01_MED_TRIAGE_HD_V2
                            WHERE AKTIF = '1'
                            AND   ISMANUAL = 'E'
                            AND   PSIKOLOGI<>5
                        )

                        SELECT A.PASIEN_ID, A.EPISODE_ID, A.RESOURCE_ID, A.POLI_ID, A.DOKTER_ID,
                            B.TRANS_ID, PSIKOLOGI,
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
                        AND 'SIRS01_' || UPPER(D.USER_ID) = UPPER(B.CREATED_BY)
                        LEFT JOIN SR01_SATUSEHAT_LOCATION E
                        ON E.LOKASI_ID = '001'
                        AND E.AKTIF = '1'
                        AND E.STATUS = 'active'
                        AND E.VALUE = A.POLI_ID
                        FETCH FIRST 5 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }
    }
?>
