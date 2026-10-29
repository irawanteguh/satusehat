<?php
    class Modelencounter extends CI_Model{

        function poliklinik($env){
            $query =
                    "
                        WITH EPISODE AS (
                            SELECT
                                A.PASIEN_ID,
                                A.EPISODE_ID,
                                A.POLI_ID,
                                A.DOKTER_ID,
                                A.TGL_KELUAR,
                                A.LAST_UPDATED_DATE
                            FROM SR01_KEU_EPISODE A
                            WHERE A.LOKASI_ID = '001'
                            AND A.AKTIF = '1'
                            AND A.JENIS_EPISODE = 'O'
                            AND A.STATUS_EPISODE = '55'
                            AND A.TGL_KELUAR IS NOT NULL
                            AND A.POLI_ID NOT IN (
                                'UGD01',
                                'UGD02',
                                'MEDIC0000000000',
                                'POLI0000000039',
                                'HEMOD0000000000'
                            )
                            AND TRUNC(A.TGL_MASUK) >= (
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
                                FROM SR01_GEN_PASIEN_MS P
                                WHERE P.LOKASI_ID = '001'
                                    AND P.AKTIF = '1'
                                    AND P.ACC_SATUSEHAT = 'Y'
                                    AND P.SATUSEHAT_ID IS NOT NULL
                                    AND P.SATUSEHAT_ID NOT IN ('NOT FOUND', 'X')
                                    AND P.ACC_SATUSEHAT_FASKES IS NOT NULL
                                    AND P.PASIEN_ID = A.PASIEN_ID
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM WEB_CO_REGISTRASI_ONLINE_HD R
                                WHERE R.LOKASI_ID = '001'
                                    AND R.AKTIF = '1'
                                    AND R.HADIR = 'Y'
                                    AND R.EPISODE_ID = A.EPISODE_ID
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_SATUSEHAT_LOCATION L
                                WHERE L.LOKASI_ID = '001'
                                    AND L.AKTIF = '1'
                                    AND L.STATUS = 'active'
                                    AND L.VALUE = A.POLI_ID
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_GEN_USER_DATA U
                                WHERE U.LOKASI_ID = '001'
                                    AND U.AKTIF = '1'
                                    AND U.IHS_ID IS NOT NULL
                                    AND U.IHS_ID <> 'NOT FOUND'
                                    AND U.DOKTER_ID = A.DOKTER_ID
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_RESUME_MEDIS R
                                WHERE R.LOKASI_ID = '001'
                                    AND R.AKTIF IN ('1', '2')
                                    AND R.APPROVE_DR = 'Y'
                                    AND R.IS_CASEMIX = 'Y'
                                    AND R.EPISODE_ID = A.EPISODE_ID
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_RM_RESUME_ICD10 R
                                WHERE R.LOKASI_ID = '001'
                                    AND R.AKTIF = '1'
                                    AND R.JNS_R = 'F'
                                    AND R.TIPE = 'IDRG'
                                    AND R.ICD10_ID NOT IN (
                                        SELECT KODE
                                        FROM SR01_SATUSEHAT_ICD_EXCLUDE
                                    )
                                    AND R.EPISODE_ID = A.EPISODE_ID
                            )
                            AND NOT EXISTS (
                                SELECT 1
                                FROM SR01_SATUSEHAT_TRANSAKSI T
                                WHERE T.LOKASI_ID = '001'
                                    AND T.AKTIF = '1'
                                    AND T.RESOURCE_TYPE = 'Encounter'
                                    AND T.JENIS = '1'
                                    AND T.ENVIRONMENT = '".$env."'
                                    AND T.PASIEN_ID = A.PASIEN_ID
                                    AND T.EPISODE_ID = A.EPISODE_ID
                            )
                            FETCH FIRST 10 ROWS ONLY
                        )
                        SELECT
                            A.PASIEN_ID,
                            A.EPISODE_ID,
                            A.POLI_ID,
                            A.DOKTER_ID,

                            D.CONDITION,

                            TO_CHAR(R.PLANNED_DATE - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(R.PLANNED_DATE - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS PLANNED,
                            TO_CHAR(R.PERIODSTART_DATE - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(R.PERIODSTART_DATE - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS PERIODSTART,
                            TO_CHAR(T.TRIAGE_DATE - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(T.TRIAGE_DATE - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS TRIAGE,
                            TO_CHAR(M.INPROGRESSSTART_DATE - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(M.INPROGRESSSTART_DATE - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS INPROGRESSSTART,
                            TO_CHAR(S.INPROGRESSEND_DATE - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(S.INPROGRESSEND_DATE - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS INPROGRESSEND,
                            TO_CHAR(GREATEST(A.TGL_KELUAR, NVL(A.LAST_UPDATED_DATE, A.TGL_KELUAR)) - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(GREATEST(A.TGL_KELUAR, NVL(A.LAST_UPDATED_DATE, A.TGL_KELUAR)) - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS FINISH,

                            P.INT_PASIEN_ID AS PATIENTMR,
                            P.NAMA AS PATIENTNAME,
                            P.SATUSEHAT_ID AS PATIENTID,
                            L.SATUSEHAT_ID AS LOCATIONID,
                            L.NAME AS LOCATIONNAME,
                            U.IHS_ID AS PRACTITIONERID,
                            UPPER(U.NAMA) AS PRACTITIONERNAME

                        FROM EPISODE A

                        LEFT JOIN SR01_GEN_PASIEN_MS P
                            ON P.PASIEN_ID = A.PASIEN_ID
                        AND P.LOKASI_ID = '001'
                        AND P.AKTIF = '1'

                        LEFT JOIN SR01_SATUSEHAT_LOCATION L
                            ON L.LOKASI_ID = '001'
                        AND L.AKTIF = '1'
                        AND L.STATUS = 'active'
                        AND L.VALUE = A.POLI_ID

                        LEFT JOIN SR01_GEN_USER_DATA U
                            ON U.LOKASI_ID = '001'
                        AND U.AKTIF = '1'
                        AND U.DOKTER_ID = A.DOKTER_ID

                        OUTER APPLY (
                            SELECT
                                LISTAGG(
                                    R.ICD10_ID || ';' || R.DIAGNOSA,
                                    '|'
                                ) WITHIN GROUP (
                                    ORDER BY R.JENIS ASC, R.DIAGNOSA ASC
                                ) AS CONDITION
                            FROM SR01_RM_RESUME_ICD10 R
                            WHERE R.LOKASI_ID = '001'
                            AND R.AKTIF = '1'
                            AND R.JNS_R = 'F'
                            AND R.TIPE = 'IDRG'
                            AND R.EPISODE_ID = A.EPISODE_ID
                            AND R.ICD10_ID NOT IN (
                                SELECT KODE
                                FROM SR01_SATUSEHAT_ICD_EXCLUDE
                            )
                        ) D

                        OUTER APPLY (
                            SELECT
                                MIN(R.CREATED_DATE) AS PLANNED_DATE,
                                MIN(R.TGL_HADIR) AS PERIODSTART_DATE
                            FROM WEB_CO_REGISTRASI_ONLINE_HD R
                            WHERE R.LOKASI_ID = '001'
                            AND R.AKTIF = '1'
                            AND R.PASIEN_ID = A.PASIEN_ID
                            AND R.EPISODE_ID = A.EPISODE_ID
                        ) R

                        OUTER APPLY (
                            SELECT
                                MIN(T.CREATED_DATE) AS TRIAGE_DATE
                            FROM SR01_MED_PRWT_TR T
                            WHERE T.LOKASI_ID = '001'
                            AND T.AKTIF = '1'
                            AND T.PASIEN_ID = A.PASIEN_ID
                            AND T.EPISODE_ID = A.EPISODE_ID
                        ) T

                        OUTER APPLY (
                            SELECT
                                MIN(M.CREATED_DATE) AS INPROGRESSSTART_DATE
                            FROM WEB_CO_MULAI_PERIKSA M
                            WHERE M.SHOW_ITEM = '1'
                            AND M.PASIEN_ID = A.PASIEN_ID
                            AND M.EPISODE_ID = A.EPISODE_ID
                        ) M

                        OUTER APPLY (
                            SELECT
                                MAX(S.CREATED_DATE) AS INPROGRESSEND_DATE
                            FROM WEB_CO_SELESAI_PERIKSA S
                            WHERE S.SHOW_ITEM = '1'
                            AND S.PASIEN_ID = A.PASIEN_ID
                            AND S.EPISODE_ID = A.EPISODE_ID
                        ) S
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

        function igd($env){
            $query =
                    "
                        WITH EPISODE AS (
                            SELECT
                                A.PASIEN_ID,
                                A.EPISODE_ID,
                                A.POLI_ID,
                                SR01_GET_DOKTERID_IGD_AKHIR(A.EPISODE_ID) AS DOKTER_ID,
                                A.TGL_MASUK,
                                A.TGL_KELUAR,
                                A.LAST_UPDATED_DATE
                            FROM SR01_KEU_EPISODE A
                            WHERE A.LOKASI_ID = '001'
                            AND A.AKTIF = '1'
                            AND A.JENIS_EPISODE = 'O'
                            AND A.STATUS_EPISODE = '55'
                            AND A.TGL_KELUAR >= DATE '2026-01-01'
                            AND A.TGL_KELUAR < TRUNC(SYSDATE) + 1
                            AND A.POLI_ID IN (
                                'UGD01',
                                'UGD02'
                            )
                            AND TRUNC(A.TGL_MASUK) >= (
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
                                FROM SR01_GEN_PASIEN_MS P
                                WHERE P.LOKASI_ID = '001'
                                    AND P.AKTIF = '1'
                                    AND P.ACC_SATUSEHAT = 'Y'
                                    AND P.SATUSEHAT_ID IS NOT NULL
                                    AND P.SATUSEHAT_ID NOT IN ('NOT FOUND', 'X')
                                    AND P.ACC_SATUSEHAT_FASKES IS NOT NULL
                                    AND P.PASIEN_ID = A.PASIEN_ID
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_SATUSEHAT_LOCATION L
                                WHERE L.LOKASI_ID = '001'
                                    AND L.AKTIF = '1'
                                    AND L.STATUS = 'active'
                                    AND L.VALUE = A.POLI_ID
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_GEN_USER_DATA U
                                WHERE U.LOKASI_ID = '001'
                                    AND U.AKTIF = '1'
                                    AND U.IHS_ID IS NOT NULL
                                    AND U.IHS_ID <> 'NOT FOUND'
                                    AND U.DOKTER_ID = SR01_GET_DOKTERID_IGD_AKHIR(A.EPISODE_ID)
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_RESUME_MEDIS R
                                WHERE R.LOKASI_ID = '001'
                                    AND R.AKTIF IN ('1', '2')
                                    AND R.APPROVE_DR = 'Y'
                                    AND R.IS_CASEMIX = 'Y'
                                    AND R.EPISODE_ID = A.EPISODE_ID
                            )
                            AND EXISTS (
                                SELECT 1
                                FROM SR01_RM_RESUME_ICD10 R
                                WHERE R.LOKASI_ID = '001'
                                    AND R.AKTIF = '1'
                                    AND R.JNS_R = 'F'
                                    AND R.TIPE = 'IDRG'
                                    AND R.ICD10_ID NOT IN (
                                        SELECT KODE
                                        FROM SR01_SATUSEHAT_ICD_EXCLUDE
                                    )
                                    AND R.EPISODE_ID = A.EPISODE_ID
                            )
                            AND NOT EXISTS (
                                SELECT 1
                                FROM SR01_SATUSEHAT_TRANSAKSI T
                                WHERE T.LOKASI_ID = '001'
                                    AND T.AKTIF = '1'
                                    AND T.RESOURCE_TYPE = 'Encounter'
                                    AND T.JENIS = '2'
                                    AND T.ENVIRONMENT = '".$env."'
                                    AND T.PASIEN_ID = A.PASIEN_ID
                                    AND T.EPISODE_ID = A.EPISODE_ID
                            )
                            FETCH FIRST 10 ROWS ONLY
                        )
                        SELECT
                            A.PASIEN_ID,
                            A.EPISODE_ID,
                            A.POLI_ID,
                            A.DOKTER_ID,

                            D.CONDITION,

                            TO_CHAR(A.TGL_MASUK - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(A.TGL_MASUK - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS PERIODSTART,
                            TO_CHAR(T.TRIAGE_DATE - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(T.TRIAGE_DATE - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS TRIAGE,
                            TO_CHAR(GREATEST(A.TGL_KELUAR, NVL(A.LAST_UPDATED_DATE, A.TGL_KELUAR)) - INTERVAL '7' HOUR, 'YYYY-MM-DD') || 'T' || TO_CHAR(GREATEST(A.TGL_KELUAR, NVL(A.LAST_UPDATED_DATE, A.TGL_KELUAR)) - INTERVAL '7' HOUR, 'HH24:MI:SS') || '+00:00' AS FINISH,

                            P.INT_PASIEN_ID AS PATIENTMR,
                            P.NAMA AS PATIENTNAME,
                            P.SATUSEHAT_ID AS PATIENTID,
                            L.SATUSEHAT_ID AS LOCATIONID,
                            L.NAME AS LOCATIONNAME,
                            U.IHS_ID AS PRACTITIONERID,
                            UPPER(U.NAMA) AS PRACTITIONERNAME

                        FROM EPISODE A

                        LEFT JOIN SR01_GEN_PASIEN_MS P
                            ON P.PASIEN_ID = A.PASIEN_ID
                        AND P.LOKASI_ID = '001'
                        AND P.AKTIF = '1'

                        LEFT JOIN SR01_SATUSEHAT_LOCATION L
                            ON L.LOKASI_ID = '001'
                        AND L.AKTIF = '1'
                        AND L.STATUS = 'active'
                        AND L.VALUE = A.POLI_ID

                        LEFT JOIN SR01_GEN_USER_DATA U
                            ON U.LOKASI_ID = '001'
                        AND U.AKTIF = '1'
                        AND U.DOKTER_ID = A.DOKTER_ID

                        OUTER APPLY (
                            SELECT
                                LISTAGG(
                                    R.ICD10_ID || ';' || R.DIAGNOSA,
                                    '|'
                                ) WITHIN GROUP (
                                    ORDER BY R.JENIS ASC, R.DIAGNOSA ASC
                                ) AS CONDITION
                            FROM SR01_RM_RESUME_ICD10 R
                            WHERE R.LOKASI_ID = '001'
                            AND R.AKTIF = '1'
                            AND R.JNS_R = 'F'
                            AND R.TIPE = 'IDRG'
                            AND R.EPISODE_ID = A.EPISODE_ID
                            AND R.ICD10_ID NOT IN (
                                SELECT KODE
                                FROM SR01_SATUSEHAT_ICD_EXCLUDE
                            )
                        ) D

                        OUTER APPLY (
                            SELECT
                                MIN(T.CREATED_DATE) AS TRIAGE_DATE
                            FROM SR01_MED_TRIAGE_HD_V2 T
                            WHERE T.LOKASI_ID = '001'
                            AND T.AKTIF = '1'
                            AND T.PASIEN_ID = A.PASIEN_ID
                            AND T.EPISODE_ID = A.EPISODE_ID
                        ) T
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

    }
?>
